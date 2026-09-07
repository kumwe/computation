# Releasing

The first non-Unreleased second-level changelog heading is the release record. Use `## X.Y.Z` and maintain SemVer;
pre-1.0 adopters pin an exact verified version. `composer.json` never carries a manually maintained version.
The shared parser is regression-tested and rejects malformed newest headings instead of reusing an older release.

Before requesting review, run `composer check` on 64-bit PHP 8.5. This includes security audit, max-level analysis,
package tests and the actual Composer ZIP installed as a dependency in a fresh no-dev authoritative consumer.
The archive must ship the charter, handoff, docs, public manifests, internal implementation, smoke tool and
example. It must exclude tests, dev tools, workflows, vendor, caches and the development lock file.

Maintainers must enable protected `main` and GitHub release immutability before the initial merge/publication.
The release job checks `github.ref_protected` before any tag mutation and requires the published release to
report `immutable: true`. It uses the scoped workflow token, never an administrator PAT or a settings bypass.
A missing protection or immutable release blocks the release/adoption gate.

Only human merge to `main` starts publication. The release workflow repeats the full gate, validates any existing
tag against main history and the changelog, creates a missing immutable version tag and publishes its GitHub
release. Already-published versions are verified idempotently. No agent manufactures a tag or release attestation.
Packagist follows the repository integration; verify visibility independently after publication.

This release covers Computation Phase 1A `contract_baseline` only. It selects no semantic API/corpus and makes no
Engine algorithm, native binding, provisioning or App integration claim. Downstream work needs independent
verification of source, exact commit/tag, archive, API/capability/service digests and installer visibility.
The embedded migration handoff must not claim its own final commit or archive hash. External evidence records
those values after publication and review.
