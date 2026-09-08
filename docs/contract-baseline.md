# Contract-baseline release prerequisite

The Version 2 objective requires an independently verified, extension-free Computation contract release
before the first App-eligible Engine stable release. That baseline has no native adapter, native service
binding or ext-kumwe_engine requirement. Engine stable precedes extension stable; only then can the
native-backed Computation successor qualify for App adoption. This prevents a dependency cycle.

## Observed publication history

On 2026-09-07 the GitHub release and tag-ref APIs, corroborated by git ls-remote, exposed only these tags:

- v0.2.0: source 669d1c46b6f243826b1b952b16224c8a87991fb1, published 2026-09-07T19:13:01Z.
- v0.2.1: source 6ac52321c2a5089f52d44e20cfa6374064960e8a, published 2026-09-07T20:08:49Z.

The two older tagged composer.json files require ext-kumwe_engine 0.0.0-dev. Version 0.2.0 additionally references
the historical Canonical JSON development branch; 0.2.1 pins its published 0.1.1 contract. Both release
records reported github_immutable=false. Neither native-backed artifact is the extension-free baseline.
The v0.1.0 release lookup returned HTTP 404 and no v0.1.0 tag was present. No usable published baseline
coordinate or independent baseline attestation was identified. Publication observations are not attestations.

Authoritative observations can be repeated from the [release collection][releases], [tag references][tags]
and tagged [0.2.0 Composer metadata][c020] and [0.2.1 Composer metadata][c021].

[releases]: https://api.github.com/repos/kumwe/computation/releases
[tags]: https://api.github.com/repos/kumwe/computation/git/matching-refs/tags/
[c020]: https://raw.githubusercontent.com/kumwe/computation/v0.2.0/composer.json
[c021]: https://raw.githubusercontent.com/kumwe/computation/v0.2.1/composer.json

## Historical reconstruction input

Untagged ancestor 3520a11a4acb075562db9dea0d23fbd4922d2bbd contains the 20 portable types, requires only
64-bit PHP 8.5, and declares a null ConfigProvider with no factories or aliases. Its manifest's proposed
0.1.0 version is a source record, not a published coordinate. Its 20 portable source files, Internal/Guard,
transport corpus and tests/run.php are byte-identical to the reviewed native successor's corresponding files.
The historical API/corpus digests are recorded in resources/contract-baseline/v1.json as reconstruction
evidence only. A commit SHA, a proposed changelog version or source equality cannot satisfy release admission.

## Required remediation and order

1. Review a separate extension-free maintenance release of kumwe/computation using the historical portable
   boundary and current portable corrections as source inputs. Preserve all existing tags and native work.
   Choose an unused release coordinate through the release process; do not fabricate a missing v0.1.0 tag
   or publish an old tree without renewed review, current manifests, handoff and security/consumer gates.
2. Its archive must contain the portable contracts and corpus with no native adapter or native binding.
   Run its complete package tests and install its no-dev archive on PHP without ext-kumwe_engine. Generate
   a Phase 1A handoff that points to the native-binding successor and records the actual selected APIs.
3. Publish that reviewed artifact and independently verify its immutable source/artifact, Composer identity,
   public API and corpus hashes, shipped handoff and external RELEASE-ATTESTATION.yaml. Only the resulting
   observed coordinates may fill the currently unresolved baseline dependency in the native release gates.
4. Engine then consumes that exact verified baseline alongside all implemented semantic-owner corpora.
   After Engine and extension stable artifacts are independently verified, qualify the native adapter
   successor and then perform the separate App provisioning and execution cutover.

The published 0.3.0 release already requires the native extension. It cannot serve as its own pre-Engine
contract baseline. Green candidate builds, native runtime tests or performance receipts do not resolve this
publication-order gap. Candidate development may continue; App-eligible stable admission remains blocked.

## Reviewed maintenance reconstruction

This branch reconstructs the Phase 1A artifact as proposed maintenance **0.1.1**, targeting
maintenance/portable-contracts. The current native main and all published versions remain preserved.
The selected coordinate is a changelog record only: publication and independent verification remain pending.
Observed v0.3.0 is source 7bff8d3c916612d374c63e56ef3f2ef76e898322, published
2026-09-08T14:36:07Z, with ext-kumwe_engine 0.0.0-dev and github_immutable=false.

The 20 portable public source files, Internal/Guard.php, and transport corpus are carried forward exactly
from that reviewed native source. Native adapter/composition files and their dependency requirements are
excluded only from this maintenance line. Current shared release integrity/retry tests remain in place, with
negative coverage for the narrowly admitted portable release branch. PHP source/API, boundary/ownership,
security, static-analysis and no-dev archive gates all remain mandatory.

No semantic runtime dependency is selected: the baseline carries only opaque, bounded transport and
closed metadata identities. Its synthetic corpus remains transport-only and never claims algorithm parity.
The native ownership manifest reflects the existing native candidate's complete ABI reservation, including
explicit plan release, while retaining candidate status. It introduces no runtime binding or fallback.

After merge and publication, independent verification must record the actual tag/commit, Composer/archive
identity, public manifest and transport corpus digests and shipped handoff. Those observations, never this
branch or a proposed version, clear the Engine prerequisite. Phase 1B cannot be qualified in this task before
the Engine and extension releases exist and are independently verified.
