# Releasing Computation

Follow the [Package release standard](package-release-standard.md) for the shared
quality gate, changelog parsing, publication and retry behavior. Complete the
[repository release setup](repository-release-setup.md) with an administrator
session before merging a release record:

```bash
bash tools/configure-release-repositories.sh --check kumwe/computation
bash tools/configure-release-repositories.sh --apply kumwe/computation
```

The required CI check is **Package gate**. Maintainers rebase reviewed PRs into the
repository's current default branch; the release workflow reruns the same quality
gate on the resulting commit and derives its release identity from that run.
A release intention in CHANGELOG.md is not evidence that publication occurred.
Keep work that is not ready for publication under `## Unreleased`.

Computation uses SemVer; pre-1.0 adopters pin an exact verified version.
`composer.json` does not carry a manually maintained version.

## Package scope and artifact qualification

Run `composer check` on supported 64-bit PHP 8.5. It includes the security audit,
max-level analysis, package tests and the actual Composer ZIP installed as a
dependency in a fresh no-dev authoritative consumer. The archive ships the charter,
handoff, docs, public manifests, internal implementation, smoke tool and example.
It excludes tests, development tools, workflows, vendor, caches and the development
lock file.

This release scope is Computation Phase 1A `contract_baseline` only. It selects no
semantic API/corpus and makes no Engine algorithm, native binding, provisioning or
App integration claim. Downstream work requires independent verification of the
source, exact commit/tag, archive, API/capability/service digests and installer
visibility. The embedded migration handoff must not claim its own final commit or
archive hash; external evidence records these after publication and review.

## Publication evidence and recovery

The maintainer performs the initial Packagist submission. Its GitHub integration
then follows tags without a registry credential in CI. Before dependent publication
or App adoption, a fresh independent verifier must bind the exact published
source/tag, archive digest, manifests, registry coordinate, license/security and
clean-consumer results in an external RELEASE-ATTESTATION.yaml. The artifact and
handoff must not invent their own final commit, checksum or publication evidence.

Use the current release workflow on the default branch to retry after correcting
repository settings. Historical mutable releases remain unchanged: enabling
immutability affects future publications, so a mutable version requires an unused
successor. Never move or delete a published tag or replace a released artifact.
An unpublished tag can be completed only on the exact commit tested by the retry.
A green PR does not replace the default-branch release result or independent
verification. Administrator credentials do not belong in Actions.
