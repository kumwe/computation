# Kumwe Computation

Bounded computation transport and explicit adapters to the native Engine. The native adapter provides exact
compatibility checks, compiler/executor services and the GenericV1 CanonicalEncoder implementation. It requires
64-bit PHP 8.5, the actual `kumwe_engine` extension, the reviewed canonical contract and PSR container 2.x.

This package does not evaluate expressions, parse decimals, normalize documents or implement canonical JSON.
It treats program, normalized document and result bytes as opaque values with explicitly supplied semantic
identities. NativeCanonicalEncoder delegates GenericV1 semantics and its exact corpus to the native Engine.

## Development

```bash
composer install --no-interaction --prefer-dist
KUMWE_NATIVE_EXPECTED_TUPLE=/absolute/path/compatibility.json composer check
```

`composer check` runs strict metadata/security/release checks, documentation and architecture checks,
deterministic public manifests, PHPStan at maximum level, PSR-12, package-owned tests and a fresh Composer
consumer that installs the actual built ZIP with no development dependencies. Run `composer test` for the
contract suite or `composer examples` for the shipped transport example.

## Contract ownership

The [charter](CHARTER.md) defines this package's scope. The [public API](docs/public-api.md) documents every
public member. The [native boundary](docs/native-boundary.md) names the implemented native owner
and separates portable metadata from Engine algorithms. The [integration notes](docs/integration.md) and
[migration handoff](MIGRATION-HANDOFF.md) describe the ordered release and adoption barriers.

Construct transport values directly. Register host-selected NativeCompatibility and apply ConfigProvider in a
request-scoped container. Compiler and Executor share one NativeAdapter. The host explicitly binds the canonical
interface to its selected encoder and releases finished native plans. See integration notes for the required
independent compatibility JSON and native test gate.

## Test ownership

Computation tests own transport shape, round trips, bounds, identity/cache-key vectors, compatibility and
ordered findings/batches. Engine owns semantic algorithms, parity corpora, fuzzing, sanitizers and performance.
The native binding owns Zend lifecycle and marshalling tests. App owns authority, composition, database,
transaction, deployment and recovery tests. This successor removes no App code or test; the later native cutover
must remove superseded package-unit cases from App while retaining integration coverage.

## Release status

The independently verified extension-free Computation baseline required before Engine stable is missing.
Published 0.2.x packages and this native-backed 0.3.0 proposal cannot satisfy that prerequisite. See the
[observed history and ordered remediation](docs/contract-baseline.md).

The changelog records a proposed release, not evidence of publication. Human merge and successful release
checks precede immutable publication. Independent release verification precedes consumer adoption. See
[releasing](docs/releasing.md) and [security](SECURITY.md).
