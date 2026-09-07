# Architecture

The canonical portable namespace is `Kumwe\Computation\`. The package has no runtime dependency beyond 64-bit PHP 8.5.
Public DTOs are final and readonly; enums and the two execution interfaces define their respective contracts.
Internal validators and deterministic metadata encoding stay outside the public API manifest.

Contract coordinates identify a semantic owner/profile/version/corpus digest without implementing that
owner's grammar. Program, compiled-artifact, normalized document and result payloads remain opaque bytes.
Execution limits are positive finite bounds. Results preserve request correlation and semantic finding order.

Plan identity incorporates semantic identity, source/program version, generation, definition/schema/options
digests and the full required capability tuple. Its domain-separated digest uses a closed typed metadata
encoder, never a generic JSON/AST/decimal implementation. Typed paths distinguish string keys and integer indexes.

Direct construction is intentional: this phase has no native adapter or provider to register. App retains all
existing execution and tests. Engine and the Zend binding are separately owned. Read the native boundary draft
for FQCN reservations, transport proposals, ABI ownership and the remaining native implementation decisions.

The architecture gate rejects foreign namespaces, runtime extension/class probing, aliases and additional
runtime dependencies. The manifest gate reflects every exported member, rejects missing documentation and
requires each public type to be covered by a capability. The archive gate rejects all test/dev files and verifies
the explicitly reviewed internal source files as well as the public manifest surface.
