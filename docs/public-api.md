# Public API

This reference describes the Computation 0.2.0 native adapter candidate, wire version 1. All short type names below
resolve under `Kumwe\Computation\` unless they are PHP built-ins. Public properties are readonly except inherited
PHP exception state. `Internal\Guard` is not public API. Native algorithms remain in the Engine; this package
owns no Engine implementation or
semantic corpus selection. See [architecture](architecture.md), [integration](integration.md) and the
[native boundary](native-boundary.md) for ownership and release barriers.

Constructors and `fromArray()` validate exact bounded values and refuse malformed input with `ExecutionRefused`.
A semantic coordinate is supplied metadata, not release verification. Opaque payload bytes are not parsed here.
DTO wire records use `wire_version: 1`, exact key/type sets and canonical base64 for binary payloads. Read the
implementation's documented wire fields alongside this API and `resources/conformance/v1.json` for fixed vectors.

The complete signatures are reflected in `resources/public-api/v1.json`; capability and service manifests describe
the same release. Inherited PHP exception methods retain PHP behavior and are not new package methods.

## `Kumwe\Computation\BatchResult`

Atomic ordered batch correspondence and caller output budgets.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    DocumentBatch $expected,
    array $results,
    ExecutionLimits $limits,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    DocumentBatch $expected,
    ExecutionLimits $limits,
): BatchResult
```

### `results()`

Return the documented validated contract value.

```php
results(
): array
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

## `Kumwe\Computation\CapabilitySet`

Explicit observed capabilities; construction does not probe a native runtime.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    string $engineVersion,
    int $abiMajor,
    string $apiVersion,
    string $buildDigest,
    array $features,
    array $contracts,
)
```

### `contracts()`

Return the documented validated contract value.

```php
contracts(
): array
```

### `features()`

Return the documented validated contract value.

```php
features(
): array
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): CapabilitySet
```

### `key()`

Return the documented validated contract value.

```php
key(
): string
```

### `supports()`

Return the documented validated contract value.

```php
supports(
    ContractIdentity $contract,
): bool
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$engineVersion` — `string`, readonly.
- `$abiMajor` — `int`, readonly.
- `$apiVersion` — `string`, readonly.
- `$buildDigest` — `string`, readonly.

## `Kumwe\Computation\CompatibilityRequirement`

Required exact ABI/API and feature/profile subset; unknown versions never downgrade.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    int $abiMajor,
    string $apiVersion,
    array $features,
    array $contracts,
)
```

### `assertSatisfiedBy()`

Return the documented validated contract value.

```php
assertSatisfiedBy(
    CapabilitySet $observed,
): void
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): CompatibilityRequirement
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$abiMajor` — `int`, readonly.
- `$apiVersion` — `string`, readonly.

## `Kumwe\Computation\CompiledProgram`

Opaque Engine artifact with exact tuple and source agreement; not native pointer storage.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    ProgramEnvelope $source,
    PlanIdentity $plan,
    string $format,
    string $bytes,
    ExecutionLimits $limits,
)
```

### `assertCompatible()`

Validate immediately before hydration; the Engine still validates artifact contents.

```php
assertCompatible(
    CapabilitySet $observed,
    string $format,
    ExecutionLimits $limits,
): void
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    ProgramEnvelope $source,
    ExecutionLimits $limits,
): CompiledProgram
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$plan` — `PlanIdentity`, readonly.
- `$format` — `string`, readonly.
- `$bytes` — `string`, readonly.

## `Kumwe\Computation\Compiler`

Coarse compiler boundary implemented by NativeAdapter for the actual extension.

### `compile()`

Compile one complete program after ProgramEnvelope::assertPlan passes; refuse atomically.

```php
compile(
    ProgramEnvelope $program,
    PlanIdentity $plan,
    ExecutionLimits $limits,
): CompiledProgram
```

## `Kumwe\Computation\ContractIdentity`

Supplied exact semantic coordinate; no semantic implementation or release verification.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    string $owner,
    string $profile,
    string $version,
    string $corpusDigest,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): ContractIdentity
```

### `key()`

Return the documented validated contract value.

```php
key(
): string
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$owner` — `string`, readonly.
- `$profile` — `string`, readonly.
- `$version` — `string`, readonly.
- `$corpusDigest` — `string`, readonly.

## `Kumwe\Computation\DocumentBatch`

Bounded ordered documents, detached from caller array references.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    array $documents,
    ExecutionLimits $limits,
)
```

### `assertForProgram()`

Check all input profiles and combined artifact/document bytes before native execution.

```php
assertForProgram(
    CompiledProgram $program,
    ExecutionLimits $limits,
): void
```

### `assertWithin()`

Return the documented validated contract value.

```php
assertWithin(
    ExecutionLimits $limits,
): void
```

### `byteSize()`

Return the documented validated contract value.

```php
byteSize(
): int
```

### `documents()`

Return the documented validated contract value.

```php
documents(
): array
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    ExecutionLimits $limits,
): DocumentBatch
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

## `Kumwe\Computation\DocumentInput`

Already normalized opaque document with explicit exact semantic identity.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    string $correlation,
    ContractIdentity $contract,
    string $bytes,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    ?ExecutionLimits $limits = NULL,
): DocumentInput
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$correlation` — `string`, readonly.
- `$contract` — `ContractIdentity`, readonly.
- `$bytes` — `string`, readonly.

## `Kumwe\Computation\ExecutionLimits`

Finite caller budgets; validates requests without running clocks, cancellation or algorithms.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    int $maxInputBytes = 16777216,
    int $maxOutputBytes = 16777216,
    int $maxDocuments = 1024,
    int $maxFindings = 4096,
    int $maxPathDepth = 32,
    int $maxParameterBytes = 4096,
    int $maxInstructions = 100000000,
    int $maxMilliseconds = 30000,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): ExecutionLimits
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$maxInputBytes` — `int`, readonly.
- `$maxOutputBytes` — `int`, readonly.
- `$maxDocuments` — `int`, readonly.
- `$maxFindings` — `int`, readonly.
- `$maxPathDepth` — `int`, readonly.
- `$maxParameterBytes` — `int`, readonly.
- `$maxInstructions` — `int`, readonly.
- `$maxMilliseconds` — `int`, readonly.

## `Kumwe\Computation\ExecutionRefused`

Safe stable failure without a user-supplied message or previous payload exception.

### `__construct()`

Construct a bounded diagnostic from a closed refusal code only.

```php
__construct(
    RefusalCode $reason,
)
```

### Public properties

- `$reason` — `RefusalCode`, readonly.

## `Kumwe\Computation\ExecutionResult`

Successful complete document result; business error findings remain result data.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    string $correlation,
    ContractIdentity $contract,
    string $bytes,
    array $findings = array (
),
)
```

### `byteSize()`

Return the documented validated contract value.

```php
byteSize(
): int
```

### `findings()`

Return the documented validated contract value.

```php
findings(
): array
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    ?ExecutionLimits $limits = NULL,
): ExecutionResult
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$correlation` — `string`, readonly.
- `$contract` — `ContractIdentity`, readonly.
- `$bytes` — `string`, readonly.

## `Kumwe\Computation\Executor`

Coarse whole-batch boundary; never calls PHP per field or expression.

### `execute()`

Validate the exact artifact tuple and input/output budgets; execute the complete batch atomically.

```php
execute(
    CompiledProgram $program,
    DocumentBatch $documents,
    ExecutionLimits $limits,
): BatchResult
```

## `Kumwe\Computation\Finding`

Ordered machine finding; localization and business rule meanings remain downstream.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    string $code,
    FindingSeverity $severity,
    FindingPath $path,
    SourceLocation $location,
    int $ordinal,
    array $parameters = array (
),
)
```

### `assertWithin()`

Return the documented validated contract value.

```php
assertWithin(
    ExecutionLimits $limits,
): void
```

### `byteSize()`

Return the documented validated contract value.

```php
byteSize(
): int
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): Finding
```

### `parameterBytes()`

Return the documented validated contract value.

```php
parameterBytes(
): int
```

### `parameters()`

Return the documented validated contract value.

```php
parameters(
): array
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$code` — `string`, readonly.
- `$severity` — `FindingSeverity`, readonly.
- `$path` — `FindingPath`, readonly.
- `$location` — `SourceLocation`, readonly.
- `$ordinal` — `int`, readonly.

## `Kumwe\Computation\FindingPath`

Typed nested map keys and indexes; numeric string keys remain strings.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    array $segments,
)
```

### `byteSize()`

Return the documented validated contract value.

```php
byteSize(
): int
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): FindingPath
```

### `segments()`

Return the documented validated contract value.

```php
segments(
): array
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

## `Kumwe\Computation\FindingSeverity`

Stable severity tokens do not turn a finding into infrastructure refusal.

### `cases()`

Return enum cases in declaration order (PHP-provided).

```php
static cases(
): array
```

### `from()`

Resolve a backing value or throw PHP ValueError (PHP-provided).

```php
static from(
    string|int $value,
): static
```

### `tryFrom()`

Resolve a backing value or return null (PHP-provided).

```php
static tryFrom(
    string|int $value,
): ?static
```

### Public properties

- `$name` — `string`, readonly.
- `$value` — `string`, readonly.

### Public constants and cases

- `Info` = `'info'`.
- `Warning` = `'warning'`.
- `Error` = `'error'`.

## `Kumwe\Computation\PlanCacheKey`

Complete deterministic plan metadata identity; owns no cache storage or invalidation.

### `__construct()`

Return the documented validated contract value.

```php
__construct(
    PlanIdentity $plan,
)
```

### Public properties

- `$value` — `string`, readonly.

## `Kumwe\Computation\PlanIdentity`

Complete cache and compilation identity; trusted generation is supplied by the host.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    ContractIdentity $contract,
    string $programVersion,
    string $sourceDigest,
    string $generation,
    string $definitionDigest,
    string $schemaDigest,
    string $optionsDigest,
    CapabilitySet $capabilities,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): PlanIdentity
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$contract` — `ContractIdentity`, readonly.
- `$programVersion` — `string`, readonly.
- `$sourceDigest` — `string`, readonly.
- `$generation` — `string`, readonly.
- `$definitionDigest` — `string`, readonly.
- `$schemaDigest` — `string`, readonly.
- `$optionsDigest` — `string`, readonly.
- `$capabilities` — `CapabilitySet`, readonly.

## `Kumwe\Computation\ProgramEnvelope`

Opaque complete semantic program; compilation input never evaluates an AST.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    ContractIdentity $contract,
    string $programVersion,
    string $bytes,
)
```

### `assertPlan()`

Return the documented validated contract value.

```php
assertPlan(
    PlanIdentity $plan,
    ExecutionLimits $limits,
): void
```

### `digest()`

Return the documented validated contract value.

```php
digest(
): string
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
    ?ExecutionLimits $limits = NULL,
): ProgramEnvelope
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$contract` — `ContractIdentity`, readonly.
- `$programVersion` — `string`, readonly.
- `$bytes` — `string`, readonly.

## `Kumwe\Computation\RefusalCode`

Stable infrastructure refusals; business findings are successful result data.

### `cases()`

Return enum cases in declaration order (PHP-provided).

```php
static cases(
): array
```

### `from()`

Resolve a backing value or throw PHP ValueError (PHP-provided).

```php
static from(
    string|int $value,
): static
```

### `tryFrom()`

Resolve a backing value or return null (PHP-provided).

```php
static tryFrom(
    string|int $value,
): ?static
```

### Public properties

- `$name` — `string`, readonly.
- `$value` — `string`, readonly.

### Public constants and cases

- `InvalidInput` = `'invalid_input'`.
- `UnsupportedVersion` = `'unsupported_version'`.
- `IncompatibleCapability` = `'incompatible_capability'`.
- `IncompatibleCorpus` = `'incompatible_corpus'`.
- `InvalidProgram` = `'invalid_program'`.
- `ExhaustedLimit` = `'exhausted_limit'`.
- `Cancelled` = `'cancelled'`.
- `InternalFailure` = `'internal_failure'`.

## `Kumwe\Computation\SourceLocation`

Portable source location without ownership of an AST.

### `__construct()`

Construct a complete validated value; does not verify an upstream release.

```php
__construct(
    string $unit,
    string $rule,
    int $ordinal,
)
```

### `fromArray()`

Hydrate the exact strict wire record and validate every declared field.

```php
static fromArray(
    array $data,
): SourceLocation
```

### `toArray()`

Export the portable versioned record in prescribed field order.

```php
toArray(
): array
```

### Public properties

- `$unit` — `string`, readonly.
- `$rule` — `string`, readonly.
- `$ordinal` — `int`, readonly.


## `Kumwe\Computation\ConfigProvider`

`__invoke()` returns the two native service factories and two package-owned interface aliases. Both concrete
services are
shared within the host's request container. Registration does not probe the extension or create services.
The host supplies `NativeCompatibility`; Compiler and Executor must share the same NativeAdapter instance.

## `Kumwe\Computation\NativeCompatibility`

`__construct()` accepts the exact host-selected `$capabilities`, `$extensionVersion`, `$embeddedEngineCommit`,
`$embeddedSourceSha256` and `$bindingBuildDigest`. The last coordinate binds the independently recorded PHP,
ABI and binding build tuple. These readonly properties identify the configured candidate without asserting
release admission. `assertObserved()` rejects a missing or mismatched coordinate before execution; extra
informational fields do not change the comparison. The binding_features string list must advertise
opaque-compiled-results/1. This common package minimum also applies to NativeCanonicalEncoder even though
its own canonical request shape is unchanged. Invalid coordinates or features produce bounded portable refusals.

## `Kumwe\Computation\NativeAdapter`

`__construct()` requires the actual extension Runtime and exact NativeCompatibility. `compile()` accepts a
portable ProgramEnvelope and bounded ExecutionLimits, calls native compilation, and retains the resulting
opaque plan handle on this adapter. `execute()` sends one ordered DocumentBatch to that same native Runtime,
requesting result_format=opaque. Engine-authored result_json bytes remain unchanged; the extension skips
constructing an unused PHP semantic result tree. Correlation, count, finding and byte-budget checks still apply.
Foreign, cloned or deserialized portable plan objects cannot acquire authority over a native plan handle.
`__clone()` refuses copying the adapter. Native failures become payload-free ExecutionRefused categories.
PHP validates transport identities and bounds; the Engine owns all program and document semantics.

`release(CompiledProgram $program): void` returns the plan's native count and source-byte capacity.
Only the same live object compiled by this adapter is accepted; foreign, copied and already released
plans raise ExecutionRefused with InvalidProgram. Native failures preserve ownership so the caller may
retry cleanup. After successful release, execute refuses the artifact. Long-lived workers and caches
must release each plan when its final use completes; this method performs no execution or algorithm.

## `Kumwe\Computation\NativeAdapterFactory`

`__invoke()` reads NativeCompatibility from the PSR container and creates a verified native adapter. Missing
or wrong configuration fails closed. The internal readiness guard requires the actual extension-owned
Runtime declaration and suppresses class autoload during presence checks.

## `Kumwe\Computation\NativeCanonicalEncoder`

`__construct()` requires Runtime, NativeCompatibility and optional upstream canonical Limits. `encode()` and
`digest()` implement the upstream CanonicalEncoder port using coarse native calls for GenericV1. The configured
capability set must include the exact canonical corpus. Native semantic findings become stable canonical
InvalidArgumentException messages. Objects are not serialized through application callbacks. Limits and all
canonicalization, UTF8, ordering, float rendering and SHA-256 semantics remain native-owned.

## `Kumwe\Computation\NativeCanonicalEncoderFactory`

`__invoke()` reads NativeCompatibility and, when registered, upstream canonical Limits from the PSR container.
It refuses wrong configuration types and creates the actual native encoder. The default limits are the
upstream GenericV1 limits; the host may supply stricter limits within that contract.
