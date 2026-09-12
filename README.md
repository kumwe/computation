# Kumwe Computation

[![Packagist version][version-badge]][packagist]
[![Computation CI][ci-badge]][ci]
[![PHP 8.5, 64-bit][php-badge]][requirements]
[![License: Apache-2.0][license-badge]](LICENSE)

Bounded computation transport and explicit adapters to the native Engine. The package provides compiler,
executor and GenericV1 canonical encoder adapters, exact compatibility checks, immutable transport values,
ordered findings and typed refusals. Engine owns the semantic algorithms; this package owns their PHP contracts
and composition.

## Installation

Provision the exact supported PHP extension before installing the Composer package:

```sh
pie install kumwe/kumwe-engine:1.0.3
composer require kumwe/computation:0.3.3
```

Runtime requirements are 64-bit PHP 8.5, `ext-kumwe_engine` **1.0.3**, `kumwe/canonical-json` **0.1.1** and
PSR Container 2.x. The PHP binding supports PHP 8.5 NTS and ZTS on Linux x86_64. PIE installation and build
toolchain provisioning belong to the host deployment process; requests and Composer scripts do not install
native modules. [Composer metadata][requirements] is the authoritative dependency declaration.

## Integration

Construct transport values directly. Register an independently approved `NativeCompatibility` tuple and apply
`ConfigProvider` in a request-scoped container. `Compiler` and `Executor` share one `NativeAdapter`; the host
explicitly binds the canonical interface to its selected encoder and releases completed plans.

The [Core contract](docs/core-contract.md) defines authorization, persistence, transaction and provisioning
boundaries. [Integration](docs/integration.md) explains tuple selection, DI configuration, lifecycle and recovery.
The [public API](docs/public-api.md), [native boundary](docs/native-boundary.md) and
[transport example](examples/typed-consumer.php) describe the supported surface.

## Package and release status

[Computation 0.3.3][release] is published and requires the exact Engine/PHP binding 1.0.3 release pair. Native
admission verifies both source archives and publisher provenance, then checks the independently derived complete
host build tuple. The portable 0.1.1 baseline remains independently verified and byte-preserved; its
[compatibility evidence](docs/contract-baseline.md) remains part of the package contract.

Published package versions, independent consumer verification and Core deployment are separate observations.
See [release requirements](docs/releasing.md), the [release record](docs/release-record.md) and
[security policy](SECURITY.md). Version badges track published releases; CI badges report the current default branch.

## Development and test ownership

```sh
composer install --no-interaction --prefer-dist
KUMWE_NATIVE_EXPECTED_TUPLE=/absolute/path/compatibility.json composer check
```

The complete gate checks metadata, security, release automation, documentation, architecture, deterministic
manifests, static analysis, coding standards, package tests, native compatibility and a fresh no-dev Composer
archive consumer. `composer test` runs transport contracts; `composer examples` runs the shipped example.

Computation owns transport shape, bounds, identities, compatibility and ordered findings. Engine owns semantic
algorithms and corpus parity; the binding owns Zend lifecycle and marshalling. Core owns authority, composition,
database, transactions, deployment and recovery. See [test ownership](docs/test-ownership.md) and [charter](CHARTER.md).

[version-badge]: https://img.shields.io/packagist/v/kumwe/computation
[packagist]: https://packagist.org/packages/kumwe/computation
[ci-badge]: https://github.com/kumwe/computation/actions/workflows/ci.yml/badge.svg?branch=main
[ci]: https://github.com/kumwe/computation/actions/workflows/ci.yml
[php-badge]: https://img.shields.io/badge/PHP-8.5%2064--bit-777BB4
[requirements]: composer.json
[license-badge]: https://img.shields.io/github/license/kumwe/computation
[release]: https://github.com/kumwe/computation/releases/tag/v0.3.3
