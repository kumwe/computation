<?php

declare(strict_types=1);

namespace Kumwe\Computation\Internal;

use Kumwe\Computation\ContractIdentity;
use Kumwe\Computation\ExecutionRefused;
use Kumwe\Computation\RefusalCode;

/**
 * Closed metadata validation; not a semantic payload validator.
 * @internal
 * @since 0.1.0
 */
final class Guard
{
    /**
     * Refuse without echoing hostile values.
     * @param bool $condition Accepted condition.
     * @return void
     * @since 0.1.0
     * @param RefusalCode $reason Safe refusal identity.
     */
    public static function require(bool $condition, RefusalCode $reason = RefusalCode::InvalidInput): void
    {
        if (PHP_INT_SIZE !== 8 || !$condition) {
            throw new ExecutionRefused($reason);
        }
    }

    /**
     * @param mixed $value Wire text.
     * @return string Valid UTF-8 text.
     * @since 0.1.0
     * @param int $maximum Maximum UTF-8 bytes.
     * @param bool $empty Whether empty text is allowed.
     */
    public static function text(mixed $value, int $maximum = 128, bool $empty = false): string
    {
        self::require(is_string($value));
        if (!is_string($value)) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        self::require(($empty || $value !== '') && strlen($value) <= $maximum && preg_match('//u', $value) === 1);
        self::require(preg_match('/[\x00-\x1f\x7f]/', $value) !== 1);
        return $value;
    }

    /**
     * @param mixed $value Identifier.
     * @return string Valid identifier.
     * @since 0.1.0
     */
    public static function token(mixed $value): string
    {
        $text = self::text($value);
        self::require(preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.:\/-]*$/D', $text) === 1);
        return $text;
    }

    /**
     * @param mixed $value Semantic version.
     * @return string Valid exact version.
     * @since 0.1.0
     */
    public static function version(mixed $value): string
    {
        $text = self::text($value, 64);
        self::require(preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $text) === 1);
        return $text;
    }

    /**
     * @param mixed $value SHA-256 hex.
     * @return string Lowercase digest.
     * @since 0.1.0
     */
    public static function digest(mixed $value): string
    {
        $text = self::text($value, 64);
        self::require(preg_match('/^[a-f0-9]{64}$/D', $text) === 1);
        return $text;
    }

    /**
     * @param mixed $value Integer.
     * @return int Bounded integer.
     * @since 0.1.0
     * @param int $minimum Inclusive minimum.
     * @param int $maximum Inclusive maximum.
     */
    public static function integer(mixed $value, int $minimum = 0, int $maximum = 2147483647): int
    {
        if (!is_int($value) || $value < $minimum || $value > $maximum) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        return $value;
    }

    /**
     * @param mixed $value Opaque bytes.
     * @return string Bounded bytes.
     * @since 0.1.0
     * @param int $maximum Maximum opaque bytes.
     */
    public static function bytes(mixed $value, int $maximum = 16777216): string
    {
        if (!is_string($value)) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        self::require(strlen($value) <= $maximum, RefusalCode::ExhaustedLimit);
        return $value;
    }

    /**
     * @param mixed $value Canonical base64.
     * @return string Decoded bytes.
     * @since 0.1.0
     * @param int $maximum Caller byte ceiling before decoding.
     */
    public static function unbase64(mixed $value, int $maximum = 16777216): string
    {
        self::base64Size($value, $maximum);
        $encoded = self::bytes($value, 22369624);
        $decoded = base64_decode($encoded, true);
        self::require(is_string($decoded));
        if (!is_string($decoded) || base64_encode($decoded) !== $encoded) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        return self::bytes($decoded, min(16777216, $maximum));
    }

    /**
     * @param mixed $value Base64 text.
     * @return int Exact decoded length before allocation.
     * @since 0.1.0
     * @param int $maximum Caller decoded byte ceiling.
     */
    public static function base64Size(mixed $value, int $maximum = 16777216): int
    {
        $maximum = self::integer($maximum, 0, 16777216);
        $encodedMaximum = intdiv($maximum + 2, 3) * 4;
        $encoded = self::bytes($value, $encodedMaximum);
        $length = strlen($encoded);
        self::require($length % 4 === 0);
        $padding = str_ends_with($encoded, '==') ? 2 : (str_ends_with($encoded, '=') ? 1 : 0);
        $bytes = intdiv($length, 4) * 3 - $padding;
        self::require($bytes >= 0 && $bytes <= $maximum, RefusalCode::ExhaustedLimit);
        return $bytes;
    }

    /**
     * @param mixed $value Object-shaped array.
     * @return array<string,mixed> Wire object.
     * @since 0.1.0
     */
    public static function object(mixed $value): array
    {
        if (!is_array($value) || count($value) > 32 || array_is_list($value)) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        $copy = [];
        foreach ($value as $key => $member) {
            self::require(is_string($key));
            if (!is_string($key)) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            $copy[$key] = $member;
        }
        return $copy;
    }

    /**
     * @param mixed $value List.
     * @return list<mixed> Bounded list.
     * @since 0.1.0
     * @param int $maximum Maximum list entries.
     */
    public static function items(mixed $value, int $maximum = 65536): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > $maximum) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        return $value;
    }

    /**
     * @param array<string,mixed> $value Object.
     * @param list<string> $keys Exact fields.
     * @return void
     * @since 0.1.0
     */
    public static function shape(array $value, array $keys): void
    {
        self::require(count($value) === count($keys));
        $actual = array_keys($value);
        sort($actual, SORT_STRING);
        sort($keys, SORT_STRING);
        self::require($actual === $keys);
        self::require(($value['wire_version'] ?? null) === 1, RefusalCode::UnsupportedVersion);
    }

    /**
     * @param array<string,int> $features Feature versions.
     * @return array<string,int> Detached sorted map.
     * @since 0.1.0
     */
    public static function features(array $features): array
    {
        self::require(count($features) <= 256);
        $copy = [];
        foreach ($features as $key => $version) {
            $token = self::token($key);
            self::require(!ctype_digit($token));
            $copy[$token] = self::integer($version, 1, 65535);
        }
        ksort($copy, SORT_STRING);
        return $copy;
    }

    /**
     * @param list<ContractIdentity> $contracts Profiles.
     * @return list<ContractIdentity> Sorted detached profiles.
     * @since 0.1.0
     */
    public static function contracts(array $contracts): array
    {
        self::require(array_is_list($contracts) && count($contracts) <= 256);
        $seen = [];
        $copy = [];
        foreach ($contracts as $contract) {
            if (!$contract instanceof ContractIdentity) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            self::require(!isset($seen[$contract->key()]));
            $seen[$contract->key()] = true;
            $copy[] = $contract;
        }
        usort($copy, static fn (ContractIdentity $a, ContractIdentity $b): int => strcmp(
            implode("\0", [$a->owner, $a->profile, $a->version, $a->corpusDigest]),
            implode("\0", [$b->owner, $b->profile, $b->version, $b->corpusDigest]),
        ));
        return $copy;
    }

    /**
     * @param mixed $value Wire features.
     * @return array<string,int> Validated detached features.
     * @since 0.1.0
     */
    public static function featuresFromWire(mixed $value): array
    {
        $features = [];
        foreach (self::items($value, 256) as $entry) {
            $entry = self::object($entry);
            $keys = array_keys($entry);
            sort($keys, SORT_STRING);
            self::require($keys === ['name', 'version']);
            $name = self::token($entry['name']);
            self::require(!isset($features[$name]));
            $features[$name] = self::integer($entry['version'], 1, 65535);
        }
        return self::features($features);
    }

    /**
     * @param mixed $value Wire profiles.
     * @return list<ContractIdentity> Profiles.
     * @since 0.1.0
     */
    public static function contractsFromWire(mixed $value): array
    {
        $contracts = [];
        foreach (self::items($value, 256) as $contract) {
            $contracts[] = ContractIdentity::fromArray(self::object($contract));
        }
        return self::contracts($contracts);
    }

    /**
     * @param array<string,int> $features Normalized features.
     * @return list<array{name:string,version:int}>
     * @since 0.1.0
     */
    public static function featureRecords(array $features): array
    {
        $records = [];
        foreach ($features as $name => $version) {
            $records[] = ['name' => $name, 'version' => $version];
        }
        return $records;
    }

    /**
     * @param mixed $value Closed validated metadata.
     * @return string Typed length encoding.
     * @since 0.1.0
     * @param int $depth Private bounded recursion depth.
     */
    public static function encode(mixed $value, int $depth = 0): string
    {
        self::require($depth <= 20);
        if ($value === null) {
            return 'N';
        }
        if (is_bool($value)) {
            return $value ? 'T' : 'F';
        }
        if (is_int($value)) {
            $text = (string) $value;
            return 'I' . strlen($text) . ':' . $text;
        }
        if (is_string($value)) {
            return 'S' . strlen($value) . ':' . $value;
        }
        if (!is_array($value)) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        $list = array_is_list($value);
        $encoded = ($list ? 'L' : 'M') . count($value) . ':';
        foreach ($value as $key => $member) {
            if (!$list) {
                self::require(is_string($key));
                $encoded .= self::encode($key, $depth + 1);
            }
            $encoded .= self::encode($member, $depth + 1);
        }
        return $encoded;
    }
}
