# Releasing the portable maintenance line

The first non-Unreleased second-level changelog heading selects the reviewed semantic version. This line
records 0.1.1, an unused portable maintenance coordinate; historical native releases and main are preserved.
No tag is manufactured for the untagged historical 0.1.0 proposal. Pre-1.0 consumers pin exact verified versions.

Human review and merge to maintenance/portable-contracts triggers the relative reusable package workflow.
The identical composer check, extension-absent tests, no-dev archive proof and release regressions run on the
actual merged commit. Publication checks out exactly that event SHA and refuses dirty or mismatched sources.
Only the committed .github/portable-release.json policy admits this repository and this maintenance branch,
and only 0.1.1-and-later 0.1 patch records may publish from it. PR and arbitrary branch pushes cannot publish.
The ordinary default-branch behavior in the common release helper remains independently regression-tested.

The helper verifies existing tags/releases and only creates absent records after confirmed HTTP 404 responses.
It never moves/deletes/replaces releases. A human merge is the publication trigger; agents create neither tags
nor attestations. Packagist follows the repository tags. Repository protection and immutable-release settings
remain optional hardening as described in package-release-standard.md, not fabricated prerequisites.

Run composer check and the release regression scripts before review. The runtime archive must ship the charter,
handoff, public manifests, portable source, docs and example; exclude tools, tests, vendor and development state.
The consumer installs that built ZIP as a dependency with no-dev authoritative loading and no native extension.

Publication is package-released only. Independently inspect source, tag, archive, Composer identity, all manifest
and corpus hashes and the shipped handoff before issuing an external RELEASE-ATTESTATION.yaml. Actual final
source/artifact observations belong outside this source tree, avoiding self-reference. Native release admission
still needs that independent baseline evidence. See contract-baseline.md for the ordered native prerequisites.

A defective release is corrected through a new reviewed patch, advisory and successor; never replace a tag.
For full common publication/retry and governance rules read package-release-standard.md.
