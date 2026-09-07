# Integration and test ownership

## Implemented scope

The package exports 20 portable transport types and six native adapter/composition types. It owns no App
runtime wiring. Native algorithms, ABI/Zend code and semantic conformance corpora remain in their owner
repositories. The released canonical contract is pinned exactly; native runtime 0.0.0-dev remains a candidate.

## Release and adoption order

First resolve the missing independently verified extension-free Computation contract baseline. Neither
published native-backed 0.2.x nor this 0.3.0 proposal can supply that pre-Engine prerequisite. The observed
history, historical source candidate and maintenance-release remedy are in [contract-baseline.md](contract-baseline.md).

Review and publish compatible package/native successors, independently verify their exact artifacts and
semantic corpus/build tuples, then provision the admitted extension before the separate App runtime cutover.
A proposed version or passing source-candidate gate does not establish stable native admission.

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

Before App integration, compare App source and its dependency/capability index with the recorded
baseline. Review exact semantic public APIs, refusal behavior and corpus digests; a coordinate is not an
attestation. Reconcile live package work before changing the public manifest. Never use a namespace alias,
mutable branch dependency or parallel PHP executor to bypass release ordering.

## Native adapter candidate

Version 0.2.0 adds explicit services requiring `ext-kumwe_engine` candidate `0.0.0-dev`, `psr/container` 2.x and
released `kumwe/canonical-json` 0.1.1. The native extension remains a candidate; no stable native release or App
adoption is asserted. The host must independently obtain the expected extension version, embedded Engine
commit, source archive SHA-256, binding build digest and complete CapabilitySet from its admitted artifact
metadata. The binding build digest covers the complete PHP, ABI and build configuration tuple; obtain it
from the build's independent expected-identity output, never from the runtime being verified.

Register that tuple as `NativeCompatibility`, then apply `ConfigProvider`. Its Compiler and Executor aliases
must resolve to the same shared NativeAdapter in a request-scoped container. Native plans cannot move across
adapter objects or requests. The host explicitly selects the CanonicalEncoder binding. Register upstream
canonical Limits only when stricter host budgets are needed. The package does not own container lifetime,
provisioning, application readiness, authorization, persistence or transactions.

No factory inspects environment variables or chooses a fallback. Missing extension, a userland Runtime shadow,
wrong tuple or wrong service type fails closed. Canonical data crosses a single Runtime call; PHP does not
walk or normalize it. Native output is returned only after complete bounded execution.

For the required integration gate, set `KUMWE_NATIVE_EXPECTED_TUPLE` to an independently configured JSON file
containing `capabilities` (the CapabilitySet transport object), `extension_version`, `embedded_engine_commit`
`embedded_source_sha256`, `binding_build_digest` and `binding_features`, then run `composer check` with the
actual extension loaded. The required binding feature is `opaque-compiled-results/1`.
Missing extension or
configuration is a failure. The no-dev consumer runs the same execution suite through the installed archive's
authoritative autoloader. The separate absence test runs with `php -n` and proves no autoload callback is used.

The host selects the global canonical encoder implementation explicitly. If this native encoder is the intended
canonical owner adapter, bind `Kumwe\CanonicalJson\CanonicalEncoder` to `Kumwe\Computation\NativeCanonicalEncoder`
in host configuration. The package provider exports the concrete encoder factory; it does not override a
dependency-owned global interface binding.

Release finished plans with `NativeAdapter::release($program)`. Foreign, copied, released and repeated-release
plans fail closed. Explicit release reclaims the bounded native plan pool for long-lived hosts; Runtime
destruction remains the final cleanup boundary. No durable artifact/cache portability is implied.

Admission also requires the native plan-release API. Older development modules share the 0.0.0-dev version
string, so an exact older host tuple must not admit a Runtime that lacks the operations this adapter needs.
The package-owned historical-module regression is `tests/native-absence.php --missing-release-api`; run it
with the actual earlier extension loaded and its matching PHP ABI. It refuses a current module as an invalid
negative fixture. The historical 81a30990 candidate exercises this refusal without a userland native shadow.

Compiled execution requests `result_format=opaque` and consumes unchanged Engine-authored result_json bytes.
This avoids an unused PHP semantic result allocation without changing result counts, correlation or findings.
The binding must advertise `opaque-compiled-results/1` in its binding_features list. NativeCompatibility
checks this common minimum for both adapter and canonical encoder composition before any execution; exact
older tuples cannot bypass it. The canonical encoder request itself is unchanged. The regression
`tests/native-absence.php --missing-opaque-results` requires an actual older module that has release() but
lacks this feature; candidate 566f6ff is such a fixture. No runtime shadow or execution fallback is involved.
