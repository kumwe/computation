# Architecture

The canonical portable namespace is `Kumwe\Computation\`. Runtime dependencies are 64-bit PHP 8.5, the native
extension, the upstream canonical contract and PSR container interfaces.
Public DTOs are final and readonly; enums and the two execution interfaces define their respective contracts.
Internal validators and deterministic metadata encoding stay outside the public API manifest.

Contract coordinates identify a semantic owner/profile/version/corpus digest without implementing that
owner's grammar. Program, compiled-artifact, normalized document and result payloads remain opaque bytes.
Execution limits are positive finite bounds. Results preserve request correlation and semantic finding order.

Plan identity incorporates semantic identity, source/program version, generation, definition/schema/options
digests and the full required capability tuple. Its domain-separated digest uses a closed typed metadata
encoder, never a generic JSON/AST/decimal implementation. Typed paths distinguish string keys and integer indexes.

Immutable transport values are directly constructed. ConfigProvider registers two shared native services in
the host request container. The host supplies exact NativeCompatibility; factories fail closed when the real
extension or matching tuple is unavailable. Engine and Zend binding implementations remain separately owned.

The architecture gate admits only exact reviewed cross-package interfaces in named adapter files. The internal
NativeRuntime guard alone may probe extension availability, with class autoload disabled. PHP semantic processing
and all unreviewed dependencies are rejected. The manifest gate reflects every exported member, rejects
missing documentation and
requires each public type to be covered by a capability. The archive gate rejects all test/dev files and verifies
the explicitly reviewed internal source files as well as the public manifest surface.
