# Portable compatibility baseline

The extension-free Computation 0.1.1 baseline is independently verified. The published
`kumwe/computation` **0.1.1** maintenance release has exact source
`fc9d049f8b675c8e19fd1672d49b5e206c9ad52a` and original GitHub ZIP SHA-256
`2d132618efa233ec3bf0c648234c4892b7eb3d5cc1c378ea91a757a91617c223`.
Its [release](https://github.com/kumwe/computation/releases/tag/v0.1.1) was published on 2026-09-08.
The [independent verifier run](https://github.com/kumwe/extension-sdk/actions/runs/34255198879)
checked the exact release/tag/registry identities, complete package gates, audit, original archive,
production authoritative autoloader and a fresh offline reinstall without the native extension.
It loaded all 20 public portable types and Internal/Guard. The original 50-file archive has no native
adapter, ConfigProvider, native service binding or extension dependency.

The corrected external [RELEASE-ATTESTATION.yaml][baseline-attestation] is independent of Computation source.
It passed the complete authoritative attestation schema and is preserved at an exact SDK Git commit.
`resources/contract-baseline/v1.json` records the YAML's own digest, original source archive digest,
API/capability/service-map/corpus identities, and the preserved original evidence ZIP's commit/path/digest.
The former receipt remains explicitly superseded historical evidence. GitHub reported `immutable: false`;
the observed exact tag and archive identities do not claim the optional platform immutability setting.

All 20 portable source files, Internal/Guard and the transport corpus in this native successor remain
byte-identical to published 0.1.1. The source preservation map is checked in native admission, ownership and
manifest gates. No algorithms have moved into the portable package, and no App implementation is removed.
The transport corpus remains SHA-256 `30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc`.

The baseline source is intentionally preserved byte for byte, including historical comments. Those comments
describe the portable release; current native implementation and ownership are documented here and in the
[Core contract](core-contract.md). Historical dependency coordinates remain in the machine evidence record.

## Native dependency identity

Published Computation 0.3.3 selects published Engine and PHP extension **1.0.3**. The source commits, archive digests,
frozen ABI and exact publisher provenance coordinates are recorded in resources/native-adapter.json.
Its status is `published-native-dependencies`: publisher OIDC provenance is not labelled an independent
release attestation. The existing `verified-native-dependencies` state remains reserved for independent
external evidence. Neither state self-attests a future Computation release.

The shared PR/default-branch gate downloads both native release bundles, verifies their exact tag commits,
archive and provenance digests, every checksum and GitHub OIDC signatures against the owners' actual
ci.yml publisher workflows. The PHP upstream verifier reproduces the binding bundle and checks its exact
embedded Engine lock. The package then builds the pinned extension and verifies its independently generated
complete build tuple before native execution and the clean no-dev archive consumer checks.

All 22 portable source/corpus files remain byte-identical to the separately verified 0.1.1 baseline.
SDK/App adoption must verify the published Computation successor and provision the same admitted native
artifact and host build tuple. Its publication and downstream application integration are separate observations.

[baseline-attestation]:
https://raw.githubusercontent.com/kumwe/extension-sdk/4dc365e35460eb598d8616aa71e6ae396c75627e/evidence/cp.yml
