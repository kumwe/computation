# Integration and test ownership

## Current scope

Phase 1A `contract_baseline` introduces 20 portable public types. It extracts ownership of execution transport
and compatibility contracts; it does not move an existing App implementation. The source baseline is App commit
`960ce8ec00cf724a7cae03e5ba09c4852c9ab54e`. `docs/consumer-inventory.json` records zero immediate App removals.

Use direct value construction and the exact documented wire arrays. Synthetic package fixtures exercise
transport only; they are not Conversion/Canonical JSON releases or semantic parity corpora. No exact semantic
API or corpus has been selected in this baseline.

## Release and adoption order

1. Human review/merge and immutable Computation baseline publication, followed by independent verification.
2. Engine settles its ABI/native layout and exact verified semantic inputs, implements the contract and proves
   semantic corpus parity, hostile bounds, sanitizer/fuzz and performance acceptance.
3. Native binding proves C ABI ownership, Zend lifecycle/marshalling, PHPT and PIE installation against the
   reviewed Engine candidate and the agreed platform/PHP matrix. Native publication follows its own gates.
4. Computation Phase 1B adds a binding adapter only after the required immutable releases are verified.
5. Extension provisioning merges before a separate App Phase 2 cutover exact-pins dependencies, replaces execution
   composition and removes superseded App implementation and package-owned unit tests.

No Phase 1A App Composer, autoload, DI, alias, provisioning or production test change is required. The proposed
native FQCNs in the joint ownership record stay native-owned and must never be implemented as Composer stubs.

## Tests by owner

| Owner | Required coverage |
|---|---|
| Computation | Transport invariants, round trips, bounds, identity vectors, exact compatibility, ordered results. |
| Engine | Algorithms, semantic corpus parity, ABI implementation, fuzzing, sanitizers and performance. |
| Native binding | Zend lifecycle, cleanup, marshalling, PHPT, installer and supported platform/PHP matrix. |
| App | Authority, composition, DB/transactions, provisioning, stale generations, delivery and recovery. |

App currently owns `Expression`, `ExpressionEvaluator`, `DecimalValue`, `RecordRuleValidator`,
`RecordExpressionValues`, `ValidationViolation` and `DocumentWriteBudget`. Their implementations and tests remain
until the ordered native cutover. That task must inventory exact test methods again against the current source,
move pure reusable invariants to their actual package owner and retain implementation/integration assertions in
App. It must not delete tests because a class has merely received a proposed future package owner.

## Drift and compatibility

Before subsequent implementation, compare App source and its dependency/capability index with the recorded
baseline. Review exact semantic public APIs, refusal behavior and corpus digests; a coordinate is not an
attestation. Reconcile live package work before changing the public manifest. Never use a namespace alias,
mutable branch dependency or parallel PHP executor to bypass release ordering.
