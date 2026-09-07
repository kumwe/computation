# Kumwe Computation

Portable computation contracts for a separately implemented native Engine. This Phase 1A `contract_baseline`
provides bounded immutable transport values, exact compatibility identities and coarse compiler/executor
interfaces. It requires 64-bit PHP 8.5 and no native extension or other runtime package.

This package does not evaluate expressions, parse decimals, normalize documents or implement canonical JSON.
It treats program, normalized document and result bytes as opaque values with explicitly supplied semantic
identities. No Conversion or Canonical JSON API/corpus has been selected by this baseline.

## Development

```bash
composer install --no-interaction --prefer-dist
composer check
```

`composer check` runs strict metadata/security/release checks, documentation and architecture checks,
deterministic public manifests, PHPStan at maximum level, PSR-12, package-owned tests and a fresh Composer
consumer that installs the actual built ZIP with no development dependencies. Run `composer test` for the
contract suite or `composer examples` for the shipped transport example.

## Contract ownership

The [charter](CHARTER.md) defines this package's scope. The [public API](docs/public-api.md) documents every
public member. The [native boundary draft](docs/native-boundary-draft.md) names the proposed native owner
and separates portable metadata from Engine algorithms. The [integration notes](docs/integration.md) and
[migration handoff](MIGRATION-HANDOFF.md) describe the ordered release and adoption barriers.

Construct values directly. There is no container provider, binding adapter, fallback interpreter or hidden
service lookup. An Engine implementation will supply the compiler and executor interfaces after the exact
contract and semantic releases are independently verified.

## Test ownership

Computation tests own transport shape, round trips, bounds, identity/cache-key vectors, compatibility and
ordered findings/batches. Engine owns semantic algorithms, parity corpora, fuzzing, sanitizers and performance.
The native binding owns Zend lifecycle and marshalling tests. App owns authority, composition, database,
transaction, deployment and recovery tests. Phase 1A removes no App code or test; the later native cutover
must remove superseded package-unit cases from App while retaining integration coverage.

## Release status

The changelog records a proposed release, not evidence of publication. Human merge and successful release
checks precede immutable publication. Independent release verification precedes native implementation or
consumer adoption. See [releasing](docs/releasing.md) and [security](SECURITY.md).
