# Releasing Computation

Follow the [Package release standard](package-release-standard.md) for the shared
quality gate, changelog parsing, publication and retry behavior. Normal publication
does not require administrator setup, active branch protection or a ruleset,
GitHub's immutable-release flag, or external attestations. Existing repository
rules and permissions still apply.

The required CI check is **Package gate**. Maintainers rebase reviewed PRs into the
repository's dynamically discovered default branch. The release workflow reruns
the complete source CI gate on that resulting commit, checks out the event's exact
`github.sha`, and verifies local `HEAD` matches it. A PR SHA is never the promised
future release identity. The newest stable SemVer changelog record selects the
version and must agree with the release manifests. An Unreleased-only changelog
does not publish; keep work that is not ready under `## Unreleased`.

Computation uses SemVer; pre-1.0 adopters pin an exact verified version.
`composer.json` does not carry a manually maintained version.

## Package scope and artifact qualification

The native-backed successor is not the contract baseline required before Engine stable. Published history
currently contains no identified extension-free baseline release. Normal tag publication does not qualify
an artifact for that dependency role. Follow [baseline remediation](contract-baseline.md) before claiming
the objective's ordered baseline, Engine, extension and native-adapter release prerequisites are satisfied.

Run `composer check` on supported 64-bit PHP 8.5. It includes the security audit,
max-level analysis, package tests and the actual Composer ZIP installed as a
dependency in a fresh no-dev authoritative consumer. The archive ships the charter,
handoff, docs, public manifests, internal implementation, smoke tool and example.
It excludes tests, development tools, workflows, vendor, caches and the development
lock file.

The 0.2.0 candidate adds native compiler/executor and GenericV1 canonical adapters.
The actual extension and independently configured compatibility tuple are mandatory
for the full gate and isolated consumer. No native provisioning or App integration is claimed. Downstream work
requires independent verification of the
source, exact commit/tag, archive, API/capability/service digests and installer
visibility. The embedded migration handoff must not claim its own final commit or
archive hash; external evidence records these after publication and review.

## Publication evidence and recovery

The maintainer performs the initial Packagist submission. Its GitHub integration
then follows tags without a registry credential in CI. Confirm `package-released`
from the successful default-branch publication run and matching published stable
release, tag and source identity. Publication does not establish `release-verified`.
Before declaring that state or SDK/App adoption, a fresh independent verifier must
bind the exact published source/tag, archive digest, manifests, registry coordinate,
license/security and clean-consumer results in an external RELEASE-ATTESTATION.yaml.
The artifact and handoff must not invent their own final commit, checksum or
publication evidence. This attestation is separate from normal publication.

Use the current release workflow on the default branch to retry after correcting
the reported failure. Existing tags and releases must match their source identity
and are never moved, deleted or replaced. Later default-branch runs may verify a
published release on an ancestor; an unpublished tag can be completed only on the
exact event commit that passed the full gate. Only a confirmed HTTP 404 permits
creation; authentication, rate-limit and server failures never authorize creation.
A release with GitHub's immutable flag disabled remains platform-mutable; accepting
it for normal publication does not make it immutable. Fix defects with an unused
successor version. A green PR does not prove publication or independent verification.

## Optional administrator hardening

[Repository release setup](repository-release-setup.md) is an explicit optional
administrator action. `--check` only audits; `--apply` changes the managed settings;
adding `--dispatch` requests a release run after setup verification:

```bash
bash tools/configure-release-repositories.sh --check kumwe/computation
bash tools/configure-release-repositories.sh --apply kumwe/computation
bash tools/configure-release-repositories.sh --apply --dispatch kumwe/computation
```

The release workflow does not change repository settings automatically. This helper
requires repository Administration access, and dispatch also needs Actions write
permission. Keep administrator credentials out of Actions. A setup audit or dispatch
is neither a normal publication prerequisite nor proof that publication succeeded.
