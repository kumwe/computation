# Core integration contract

Computation owns bounded portable transport, explicit native adapters and PHP service composition. Engine owns
algorithms and semantic corpora; the extension owns Zend marshalling, C ABI invocation and native allocation.
Core owns authorization, persistence, transactions, reference resolution, provisioning and operational recovery.

## Service composition

The host supplies an independently approved `NativeCompatibility` tuple and applies `ConfigProvider` to its
request-scoped container. `Compiler` and `Executor` resolve to the same shared `NativeAdapter`. The host chooses
its global `Kumwe\CanonicalJson\CanonicalEncoder` implementation explicitly; the provider exports the concrete
`NativeCanonicalEncoder` factory without overriding a dependency-owned interface binding.

No factory reads environment variables, provisions extensions or chooses a fallback. Missing extensions,
userland Runtime shadows, wrong tuples and wrong service types fail closed. Semantic inputs and result bytes
remain opaque; no decimal parser, expression evaluator or document normalizer is implemented here.

## Native admission and lifetime

The current dependency requires Engine and binding 1.0.3. Verify their exact signed source artifacts, then derive
the complete expected PHP/ABI/build tuple independently of the loaded runtime. Capability, corpus, extension,
embedded-source and build identities must match. `opaque-compiled-results/1` and the plan-release API are required.

Compiled plans belong to their adapter and request. Call `NativeAdapter::release($program)` when finished;
foreign, copied, released and repeated-release plans are refused. Runtime destruction remains the final cleanup
boundary. No durable plan-cache portability or host generation policy is implied.

## Consumer verification and recovery

Core retains integration tests for authorization, transactions, storage, deployment, stale generations and
recovery. Library behavior, boundary and conformance tests remain with their package owners. Remove duplicate
legacy implementation assertions only when their corresponding implementation is removed and package ownership
is established; retain Core composition coverage.

An artifact coordinate is not an independent attestation. Verify the actual published package and native tuple
before deployment, and qualify the representative workload separately. Recovery restores the complete known-good
PHP, extension, Engine, Composer lock and configuration image; no loaded shared object is replaced in place.

See [integration](integration.md), [native boundary](native-boundary.md), [test ownership](test-ownership.md),
[release record](release-record.md) and [security](../SECURITY.md).
