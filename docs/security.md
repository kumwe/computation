# Security contract

Opaque bytes and supplied semantic coordinates are untrusted transport values. Validation here establishes
shape, bounded size and identity consistency; it does not authorize the operation or attest a semantic release.
Hosts must select independently verified semantic/native artifacts before execution. Engine must validate
compiled artifacts and enforce execution budgets atomically.

Constructors validate types and lengths before costly payload decoding. Wire records have an exact key set and
version; base64 is strict and canonical. Caller limits may reduce fixed ceilings. Limits bound each payload,
aggregate byte counts, document/finding counts, parameter sizes and path depth. Arithmetic must reject overflow.

Arrays are rebuilt from validated scalar values so PHP references cannot mutate an immutable DTO after
construction. Payloads never contain callbacks, objects or resources. The internal metadata encoder accepts
only its closed vocabulary; it is not a serializer for arbitrary or recursive user graphs.

Typed refusal codes distinguish infrastructure/contract failure from successful business findings. Refusal
messages must be bounded, safe diagnostics and must not echo hostile payloads. Successful batches are complete,
ordered one-to-one responses; partial output is prohibited. Finding parameters contain bounded machine data
for host localization, with no native diagnostic text promoted to a business finding.

Engine allocator use, callback prohibition, pointer lifetimes, concurrency and cancellation remain native
implementation obligations. The baseline reserves no autoloadable native classes and exposes no native pointer.
A portable contract test is not sanitizer, fuzzing or parity evidence for Engine.
