# Releasing

The first non-Unreleased second-level changelog heading is the release record. Use `## X.Y.Z` and maintain SemVer;
pre-1.0 adopters pin an exact verified version. `composer.json` never carries a manually maintained version.
The shared parser is regression-tested and rejects malformed newest headings instead of reusing an older release.

Before requesting review, run `composer check` on 64-bit PHP 8.5. This includes security audit, max-level analysis,
package tests and the actual Composer ZIP installed as a dependency in a fresh no-dev authoritative consumer.
The archive must ship the charter, handoff, docs, public manifests, internal implementation, smoke tool and
example. It must exclude tests, dev tools, workflows, vendor, caches and the development lock file.

Maintainers must enable protected `main` and GitHub release immutability before the initial merge/publication.
The release job reads the current `main` branch protection through GitHub's branch API before any tag mutation
and requires the published release to report `immutable: true`. It uses the scoped workflow token, never an
administrator PAT or a settings bypass. Reading current branch metadata lets a retry observe repaired protection.
A missing protection or immutable release blocks the release/adoption gate.

## Repository setup and recovery

Packagist integration and branch protection are separate settings. A failure reporting that `main` must be
protected means publication stopped before creating its tag; reconnecting Packagist cannot resolve it.
Committed workflow files and ruleset JSON do not activate repository settings by themselves.

Before merging a release or its repair PR, a repository administrator must complete both settings:

1. Open [repository rulesets](https://github.com/kumwe/computation/settings/rules), choose **New ruleset →
   Import a ruleset**, and select [release-main-ruleset.json](../.github/release-main-ruleset.json).
   Review the imported ruleset, ensure enforcement is **Active**, and create it. It targets only `main`,
   requires a pull request and the passing `PHP 8.5` check against the current base, blocks force pushes and
   deletion, and gives nobody a bypass. The approval count is zero so a sole maintainer can merge their own
   reviewed PR; the PR and required check remain mandatory. If equivalent protection already exists, retain
   it and verify it is active rather than adding a duplicate ruleset.
2. Open [repository settings](https://github.com/kumwe/computation/settings), find **Releases**, and select
   **Enable release immutability**. An organization policy that enforces immutability also satisfies this
   prerequisite. Enable this before publication: changing the setting does not retroactively protect an
   already published mutable release.

An administrator can alternatively apply the same settings from the repository root using their own
authenticated GitHub CLI. The first command creates a new ruleset; run it only when equivalent protection
has not already been configured. These are maintainer setup commands, never workflow steps:

```bash
gh api --method POST repos/kumwe/computation/rulesets \
  --input .github/release-main-ruleset.json
gh api --method PUT repos/kumwe/computation/immutable-releases
```

Verify the configuration before merging:

```bash
set -euo pipefail
gh api repos/kumwe/computation/branches/main \
  | jq -e '.name == "main" and .protected == true'
gh api repos/kumwe/computation/immutable-releases \
  | jq -e '.enabled == true'
```

The immutability settings endpoint requires administrator read access. Failure to read it with the workflow
token does not establish whether the setting is enabled; check with an administrator session or in settings.
Keep administrator credentials out of Actions. The workflow independently verifies the published release's
immutability through the release API using its existing scoped token.

After setup, merge the reviewed repair PR with its package CI passing. The resulting `main` push runs the full
release workflow and publishes the recorded version if missing. Rerunning an older failed workflow uses its
older workflow code, so merge the repair first. Do not create a replacement tag or increment the changelog just
to work around a settings failure. Verify the release job succeeds and the version appears on Packagist before
downstream adoption; settings setup alone is not publication evidence.

## Publication and adoption

Only human merge to `main` starts publication. The release workflow repeats the full gate, validates any existing
tag against main history and the changelog, creates a missing immutable version tag and publishes its GitHub
release. Already-published versions are verified idempotently. No agent manufactures a tag or release attestation.
Packagist follows the repository integration; verify visibility independently after publication.

This release covers Computation Phase 1A `contract_baseline` only. It selects no semantic API/corpus and makes no
Engine algorithm, native binding, provisioning or App integration claim. Downstream work needs independent
verification of source, exact commit/tag, archive, API/capability/service digests and installer visibility.
The embedded migration handoff must not claim its own final commit or archive hash. External evidence records
those values after publication and review.
