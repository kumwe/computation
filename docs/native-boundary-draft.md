# Computation contract baseline and native boundary draft

Status: independently reviewed; required refinements incorporated before implementation.
Scope: Computation Phase 1A, MIG-2026-008 / KUMWE-CS-2026-008 / NRM-2026-010.

This document assigns transport ownership. It does not implement or release an Engine, select an
unreleased semantic dependency, claim algorithm parity, or authorize App adoption.

## Semantic and runtime boundary

Computation owns immutable execution metadata, identities, finite limits, ordered batches/findings,
refusals and coarse compiler/executor interfaces. Program, document and result payloads are opaque
bytes tagged with an exact semantic owner/profile/version/corpus identity. Opaque means no decimal
parsing, AST validation, document normalization or canonical JSON algorithm occurs here.

No Conversion or Canonical JSON public API/corpus is selected in this baseline. Those semantic owners
remain mandatory verified inputs of whichever Engine capability eventually implements them. A
ContractIdentity is a supplied coordinate, not an assertion that a release or corpus was verified.
Package tests use a clearly synthetic transport-only profile, not a counterfeit semantic release.

Current App Expression, ExpressionEvaluator, DecimalValue, RecordRuleValidator, RecordExpressionValues,
ValidationViolation and DocumentWriteBudget retain their existing ownership and tests. The neutral
contracts below are new. There is no verbatim App type removal in Phase 1A.

## Portable public types

All listed types have the prefix `Kumwe\Computation\`. No other portable public type is planned.

| Type | Contract |
|---|---|
| ContractIdentity | Owner package, profile, version, SHA-256 corpus digest; exact bounded identity. |
| CapabilitySet | Engine/API/build/ABI identities, sorted unique feature versions and semantic identities. |
| CompatibilityRequirement | Exact API/ABI major and required feature/profile/corpus subset; fail closed. |
| ExecutionLimits | Positive finite input/output bytes, documents, findings, path depth, parameter bytes. |
| ProgramEnvelope | ContractIdentity, program version and bounded opaque source bytes. |
| PlanIdentity | Host-supplied generation, definition/schema SHA-256, program digest, options digest and tuple. |
| PlanCacheKey | Domain-separated SHA-256 over an unambiguous length-prefixed complete PlanIdentity. |
| CompiledProgram | PlanIdentity, exact Engine-owned artifact-format token and bounded opaque artifact bytes. |
| DocumentInput | Correlation token, ContractIdentity and opaque normalized document bytes. |
| DocumentBatch | Nonempty ordered bounded inputs with unique correlation identities and aggregate byte bound. |
| FindingPath | Bounded typed string/index segments; distinguishes map key `"0"` from list index `0`. |
| FindingSeverity | `info`, `warning`, `error`; stable wire tokens. |
| SourceLocation | Program unit/rule token and nonnegative ordinal; no AST ownership. |
| Finding | Stable code, severity, path, location, ordinal and bounded string/int/bool/null parameters. |
| ExecutionResult | Correlation, opaque result bytes and monotonically ordered findings; no human prose. |
| BatchResult | Exact one-to-one ordered request/result correlation; aggregate result/finding bounds. |
| RefusalCode | Stable closed contract/infrastructure failures, distinct from successful business findings. |
| ExecutionRefused | Safe bounded diagnostic with RefusalCode; does not include hostile payload text. |
| Compiler | `compile(ProgramEnvelope, PlanIdentity, ExecutionLimits): CompiledProgram`. |
| Executor | `execute(CompiledProgram, DocumentBatch, ExecutionLimits): BatchResult`. |

All DTOs are final and readonly; enums/interfaces are deliberately not DTOs. Arrays are validated and
rebuilt from scalar values to detach PHP references. Payloads are strings only: no objects, resources,
callbacks, recursive PHP graphs or executable semantic models are accepted.

## Stable serialization and limits

Baseline wire version is integer 1. Every portable record exposes `toArray()` and strict `fromArray()`
where useful for external round trips; inputs must contain exactly the documented keys and types.
Scalar order, list order, map order and typed path segments are explicit. Binary payloads use strict
canonical base64 in wire arrays; decoding is preceded by encoded-size checks. Unknown fields or wire
versions fail closed. Plain arrays are the PHP boundary, not generic canonical JSON documents.

A single internal deterministic length-prefix encoder serializes only this closed metadata vocabulary
for identity hashing and fixture bytes: null, boolean, integer, string and ordered list/map records.
Every scalar carries a type tag; every byte string and collection carries its length. Floats, objects,
resources and recursive arrays are forbidden. This encoder never canonicalizes opaque payloads or
arbitrary user data and is not a public generic serializer. Map keys are prescribed record keys, with
variable metadata maps normalized in their owning constructor.

Hard ceilings: 16 MiB per opaque payload, 64 MiB aggregate input/output, 4096 documents, 65536 findings,
64 path segments, 64 parameters per finding, 16 KiB parameter bytes, 4096 bytes per string parameter,
128-byte identity tokens and 256 advertised features/profiles. Caller limits can lower these ceilings;
constructors reject zero, negative, overflow and excessive limits before payload processing.
Program compiler execution budgets are finite `maxInstructions` and `maxMilliseconds`, supplied in
ExecutionLimits; Engine enforces them and returns typed refusal without partial output. They are not
PHP callbacks, ambient clocks or an invitation to implement a PHP executor.
The hard ceilings are 1000000000 instructions and 600000 milliseconds. These contracts validate only
the requested budget; this baseline neither measures elapsed time nor executes cancellation.

The metadata identity grammar is byte-exact: null is `N`; false is `F`; true is `T`; an integer is
`I` followed by its decimal spelling byte length, `:`, and canonical signed decimal spelling; a string
is `S` followed by byte length, `:`, and unchanged bytes. A list is `L<count>:` followed by its encoded
values. A map is `M<count>:` followed by alternating encoded string keys and values in the owning
record's documented field order. Length/count is canonical unsigned decimal with no leading zero.
Integers are signed 64-bit; floats are forbidden. There are no terminators or ambient locale rules.
Records use the order of their documented `toArray()` fields. Features are sorted by ASCII token;
semantic profiles are sorted by owner/profile/version/corpus. All duplicate identities are rejected.
This private identity encoding is not an Engine request wire encoding or a generic canonical JSON API.
The Engine header/layout and complete ABI request transport remain a future native-owned freeze.
Feature and finding-parameter names cannot be digit-only strings: PHP coerces numeric array keys and
would erase their declared string identity. Features serialize as ordered name/version records, with
`[]` for no features. Empty finding parameters serialize as `[]`; nonempty parameters are a sorted
string-key map. Empty paths and contract lists also use `[]`. These conventions are corpus-tested.
Tokens use ASCII `[A-Za-z0-9][A-Za-z0-9_.:/-]*`, at most 128 bytes, without trimming or control bytes.
Package owners use two lowercase `[a-z0-9][a-z0-9_.-]*` components separated by `/`. Versions are three
unsigned decimal components without leading zeroes, bounded to 64 bytes. Digests are 64 lowercase hex
digits. Paths admit nonnegative indexes through 2147483647 and valid UTF-8 string keys through 128 bytes;
an empty string key is distinct from the empty root path. Machine string parameters admit valid UTF-8
through 4096 bytes, including empty strings; all text rejects C0 controls and DEL.

## Plan and compiled artifact identity

PlanIdentity includes the exact semantic identity, program version and source SHA-256; host-selected
generation; definition/schema SHA-256; compilation-options SHA-256; complete required capability tuple.
The cache key begins with the fixed byte domain `kumwe.computation.plan.v1` and encodes all these fields
using the documented typed length-prefix format. Golden vectors will lock each field and type boundary.

CompiledProgram carries an opaque artifact generated by Engine. Its format token and exact compatible
Engine build/API/ABI/profile/corpus tuple are mandatory. This does not promise persistence portability
across Engine builds and does not expose private VM bytecode. Hydration is accepted only by the exact
declared artifact format/tuple. Native pointer bytes are never a portable artifact. Corrupt or unknown
artifacts are refused. Cache lifetime and invalidation remain host decisions; this baseline adds no cache.
Compilation validates ProgramEnvelope against PlanIdentity's semantic identity, program version and
source digest. CompiledProgram construction repeats this agreement and its hydration validation
requires exact format and engine/build/API/ABI/profile/corpus identity. A binding cannot skip those checks.

## Findings, errors and atomicity

Finding ordinal records semantic declaration/execution order. A result rejects duplicate/decreasing
ordinals; it does not lexically sort business findings or rewrite source order. Typed paths preserve
string keys and integer indexes. Parameters are bounded machine data for host localization; no message
or native/compiler text is exposed as a business semantic result.

Baseline refusal codes: invalid_input, unsupported_version, incompatible_capability, incompatible_corpus,
invalid_program, exhausted_limit, cancelled, internal_failure. Wire codes are strings. The proposed C
status numbers are respectively 1 through 8, with success 0; unknown numbers are never success.

A valid result may contain error-severity business findings. Infrastructure refusal throws
ExecutionRefused and produces no BatchResult. Batch success contains exactly one result per input in
the same order. Partial output is prohibited in baseline version 1. Cancellation is an explicit native
operation resource condition; a caller cannot inject a callback. No retry or fallback is implied.
BatchResult validation takes the expected DocumentBatch and checks count, order, correlation and exact
result semantic identity. Boundary byte budgets include opaque payloads and finding path/parameter
bytes. Every finding and path token is UTF-8 checked and byte bounded. Caller limits are applied at the
compile/execute/result validation boundary, after constructor hard ceilings and before native use.

## Joint native FQCN ownership proposal

| Exact FQCN | Runtime owner | Public surface |
|---|---|---|
| Kumwe\Engine\Runtime | kumwe/kumwe-engine, Zend only | Three coarse array calls described below. |
| Kumwe\Engine\Exception\BindingFailure | kumwe/kumwe-engine, Zend only | Safe infrastructure status. |

Runtime's three coarse calls exchange the closed version-1 arrays above. The later Computation Phase 1B
adapter converts those arrays to/from the portable types and owns compatibility checks. Runtime does
not implement late-loaded Composer interfaces during MINIT. Native stub files are never autoloadable.
The listed native types are proposed declarations, not PHP classes implemented by this package.
Canonical JSON and other semantic packages own their FQCNs; the joint manifest will enumerate current
portable and reserved native declarations, and reject duplicate or cross-namespace ownership.
The Runtime methods are `capabilities(): array`, `compile(array): array`, and `execute(array): array`.
BindingFailure carries infrastructure status and never owns business findings.

## C ABI proposal

Prefix: `kumwe_engine_v1_`. The extension calls only this C ABI. All other Engine symbols are hidden.
The concrete header and layout must be reviewed/frozen by Engine before native implementation.

`view` is a struct-size/version-negotiated immutable pointer plus uint64 byte length. The caller owns
input memory through the call. `buffer` is an opaque Engine-owned result handle. All request/response
bytes use the versioned closed transport described above, not raw host struct layouts or PHP serialization.

| Function | Ownership and result |
|---|---|
| capabilities(view request, buffer** response) -> uint32 status | Complete observed tuple; bounded response. |
| compile(view request, buffer** response) -> uint32 status | Complete program to opaque artifact. |
| execute(view request, buffer** response) -> uint32 status | Complete batch to atomic ordered results. |
| buffer_view(const buffer*, view* output) -> uint32 status | Borrowed immutable view valid until buffer release. |
| buffer_release(buffer*) -> void | Matching Engine allocator release exactly once; null is harmless. |

No C++ exception crosses an export. Every nonzero status returns a null response. Success returns a
non-null response, including when the response payload has zero bytes. Safe diagnostic identity is
the stable status alone. The implementation cannot retain input pointers or invoke PHP callbacks.
Each operation is independent and reentrant; a result buffer is immutable and may be read concurrently
until its owner releases it. Concurrent release/access is forbidden. Binding owns exactly-once cleanup
on exceptions and request shutdown. Native compilation/plan handles stay private; no address crosses PHP.

Native implementation must settle actual struct alignment/layout, ownership misuse defenses, supported
platforms, cancellation implementation and sanitizer evidence. This reviewed proposal is not such evidence.

## Package acceptance and downstream barriers

Phase 1A tests own every new invariant, round trip, identity golden vector, compatibility refusal,
hostile/bounded input, ordered batch/finding rule, API/capability manifest and FQCN collision guard.
Clean Composer consumers install the built no-dev ZIP as a dependency without the native extension.

Engine owns algorithms, ABI implementations, native corpus parity/fuzz/sanitizers/benchmarks. The binding
owns Zend/lifecycle/marshalling/PIE/PHPT evidence. App retains composition/authority/database/transaction/
deployment/recovery tests. Existing App execution and its tests remain until the ordered native cutover.

Human merge, release and independent verification of this baseline precede Engine stable publication.
Phase 1B waits for verified baseline/Engine/extension releases. Extension provisioning merges before
Computation Phase 2 switches App execution and removes superseded code/package-owned unit tests.
