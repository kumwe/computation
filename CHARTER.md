# Kumwe Computation charter

## Responsibility

Own portable, finite, immutable execution metadata and transport contracts: semantic identity coordinates,
Engine capability tuples, exact compatibility requirements, program and compiled-artifact envelopes,
complete plan/cache identities, ordered document batches, machine findings, results and typed refusals.
Own the coarse `Compiler` and `Executor` contracts and explicit native implementations, including
CanonicalEncoder. All portable public declarations use the canonical
`Kumwe\Computation\` namespace; `Internal` declarations are implementation details.

## Scope of the native adapter candidate

Program and document transport remain opaque bytes. Semantic identities describe coordinates supplied by a
host; construction does not attest that the owner, release or corpus was verified. Native adapters require the
actual extension and compare its complete build/corpus tuple with independently configured expectations.
CanonicalEncoder uses the upstream GenericV1 contract. There is no App class or test removal.

The required extension-free contract-baseline release has not been identified in published history.
This native-backed successor cannot be its own pre-Engine prerequisite; see docs/contract-baseline.md.

## Exclusions

- Engine algorithms, decimal parsing, expression evaluation and document normalization.
- Canonical JSON semantics or a public generic serialization facility.
- Native allocation, C ABI implementation, Zend registration, PIE and extension provisioning.
- Authorization, actor/site identity, active generations, persistence and transactions.
- Global service locators, automatic provisioning and PHP execution fallbacks.
- Plan-cache lifetime, stale-generation decisions and host localization or rendering.

## Dependencies and native ownership

Runtime dependency ceiling: 64-bit PHP 8.5, ext-kumwe_engine, kumwe/canonical-json and psr/container. The current
constraints identify development candidates; immutable release admission remains a separate required gate.
Native Engine and binding declarations remain owned by their repositories. Native FQCNs must never appear as
autoloadable PHP classes or runtime stubs in this package. Static analysis declarations are excluded from the archive.

## Package contract

Public members are documented and reflected into the API manifest. Capability and service manifests cover
the same reviewed surface. DTOs detach PHP array references, validate strict wire records and finite limits,
and preserve semantic declaration order. Cache identities include every compatibility-relevant coordinate.
Refusals are infrastructure/contract failures; error-severity business findings may occur in successful results.
No partial batch success is permitted by wire version 1.

## Tests and release

The package owns unit tests for all its invariants and the actual no-dev consumer archive. Native semantics
and host integration retain their own test owners. Do not duplicate Computation's unit suite in App at adoption.
Publish through the reviewed changelog release workflow after human merge. Independent artifact/source/API
verification is required before a later task adopts the release. A package release is not App completion.
