<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use InvalidArgumentException;
use Kumwe\CanonicalJson\CanonicalEncoder;
use Kumwe\CanonicalJson\FindingCode;
use Kumwe\CanonicalJson\Limits;
use Kumwe\Computation\Internal\NativeRuntime;
use Kumwe\Engine\Exception\BindingFailure;
use Kumwe\Engine\Runtime;

/**
 * One coarse native call for each canonical operation; PHP never walks or normalizes the input graph.
 * @since 0.2.0
 */
final readonly class NativeCanonicalEncoder implements CanonicalEncoder
{
    /**
     * @var string Exact canonical corpus advertised by the verified candidate tuple.
     * @since 0.2.0
     */
    private string $corpusDigest;

    /**
     * @param Runtime $runtime Native request-scoped transport object.
     * @param NativeCompatibility $compatibility Exact host-selected tuple.
     * @param Limits $limits Canonical profile budgets, enforced by Engine.
     * @throws ExecutionRefused When the runtime does not advertise the exact generic profile.
     * @since 0.2.0
     */
    public function __construct(
        private Runtime $runtime,
        NativeCompatibility $compatibility,
        private Limits $limits = new Limits(),
    ) {
        NativeRuntime::assertAvailable();
        try {
            $compatibility->assertObserved($runtime->capabilities());
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        foreach ($compatibility->capabilities->contracts() as $contract) {
            if (
                $contract->owner === 'kumwe/canonical-json'
                && $contract->profile === 'kumwe-canonical-json/generic-v1'
            ) {
                $this->corpusDigest = $contract->corpusDigest;
                return;
            }
        }
        throw new ExecutionRefused(RefusalCode::IncompatibleCorpus);
    }

    /**
     * @param mixed $value Original PHP value; no callbacks or serializers are invoked.
     * @return string Complete canonical bytes produced by Engine.
     * @throws InvalidArgumentException With one native canonical FindingCode on semantic refusal.
     * @throws ExecutionRefused On native infrastructure failure.
     * @since 0.2.0
     */
    public function encode(mixed $value): string
    {
        return $this->operation($value, 'encode', 'output');
    }

    /**
     * @param mixed $value Original PHP value under the identical encoding limits.
     * @return string Native lowercase SHA-256 over the exact canonical byte stream.
     * @throws InvalidArgumentException With one native canonical FindingCode on semantic refusal.
     * @throws ExecutionRefused On native infrastructure failure.
     * @since 0.2.0
     */
    public function digest(mixed $value): string
    {
        return $this->operation($value, 'digest', 'sha256');
    }

    /**
     * @param mixed $value Unmodified input graph.
     * @param string $operation Closed native operation name.
     * @param string $field Native result field.
     * @return string Complete native result.
     * @since 0.2.0
     */
    private function operation(mixed $value, string $operation, string $field): string
    {
        try {
            $result = $this->runtime->execute([
                'wire_version' => 1,
                'profile' => 'kumwe-canonical-json/generic-v1',
                'corpus_digest' => $this->corpusDigest,
                'operation' => $operation,
                'input' => $value,
                'limits' => [
                    'maxDepth' => $this->limits->maxDepth,
                    'maxNodes' => $this->limits->maxNodes,
                    'maxOutputBytes' => $this->limits->maxOutputBytes,
                    'maxInputBytes' => $this->limits->maxInputBytes,
                ],
            ]);
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        if (isset($result['finding'])) {
            $code = is_string($result['finding']) ? FindingCode::tryFrom($result['finding']) : null;
            if ($code === null) {
                throw new ExecutionRefused(RefusalCode::InternalFailure);
            }
            throw new InvalidArgumentException($code->value);
        }
        if (!is_string($result[$field] ?? null)) {
            throw new ExecutionRefused(RefusalCode::InternalFailure);
        }
        return $result[$field];
    }
}
