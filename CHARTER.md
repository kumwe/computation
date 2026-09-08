# Kumwe Computation charter

## Responsibility

Own portable, finite, immutable execution metadata and transport contracts: semantic identity coordinates,
Engine capability tuples, exact compatibility requirements, program and compiled-artifact envelopes,
complete plan/cache identities, ordered document batches, machine findings, results and typed refusals.
Own the coarse `Compiler` and `Executor` contracts. All portable public declarations use the canonical
`Kumwe\Computation\` namespace; `Internal` declarations are implementation details.

## Scope of Phase 1A

This is the `contract_baseline` phase. Inputs and outputs are opaque strings. Semantic identities describe
coordinates supplied by a host; construction does not attest that the owner, release or corpus was verified.
No semantic package API/corpus is selected. This package requires 64-bit PHP 8.5 alone and runs without a native
extension. There is no App class removal and no assertion of expression or document algorithm parity.

## Exclusions

- Engine algorithms, decimal parsing, expression evaluation and document normalization.
- Canonical JSON semantics or a public generic serialization facility.
- Native allocation, C ABI implementation, Zend registration, PIE and extension provisioning.
- Authorization, actor/site identity, active generations, persistence and transactions.
- Container providers, adapters, aliases, global service locators and PHP execution fallbacks.
- Plan-cache lifetime, stale-generation decisions and host localization or rendering.

## Dependencies and native ownership

Runtime dependency ceiling: 64-bit PHP 8.5. Native Engine and binding declarations remain owned by their own
repositories. The proposed native FQCNs must never appear as autoloadable classes or stubs in this package.
A later adapter phase may depend only on verified immutable native and semantic releases. Joint ownership,
ABI layout and native evidence must be independently reviewed before implementation.

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
