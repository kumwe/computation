# Contract-baseline release prerequisite

The extension-free Computation Phase 1A prerequisite is now independently verified. The published
`kumwe/computation` **0.1.1** maintenance release has exact source
`fc9d049f8b675c8e19fd1672d49b5e206c9ad52a` and original GitHub ZIP SHA-256
`2d132618efa233ec3bf0c648234c4892b7eb3d5cc1c378ea91a757a91617c223`.
Its [release](https://github.com/kumwe/computation/releases/tag/v0.1.1) was published on 2026-09-08.
The [independent verifier run](https://github.com/kumwe/extension-sdk/actions/runs/34250329345)
checked the exact release/tag/registry identities, complete package gates, audit, original archive,
production authoritative autoloader and a fresh offline reinstall without the native extension.
It loaded all 20 public portable types and Internal/Guard. The original 50-file archive has no native
adapter, ConfigProvider, native service binding or extension dependency.

The external [RELEASE-ATTESTATION.yaml artifact][baseline-attestation]
is independent of the Computation source. `resources/contract-baseline/v1.json` records the artifact ZIP
SHA-256, member digest, original source archive digest and API/capability/corpus identities. GitHub reported
`immutable: false`; the observed exact tag and archive identities are recorded without claiming the
optional platform immutability setting. Preserve these external artifacts before Actions retention expires.

All 20 portable source files, Internal/Guard and the transport corpus in this native successor remain
byte-identical to published 0.1.1. The source preservation map is checked in native admission, ownership and
manifest gates. No algorithms have moved into the portable package, and no App implementation is removed.
The transport corpus remains SHA-256 `30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc`.

## Historical reconstruction and publication history

Historical untagged source `3520a11a4acb075562db9dea0d23fbd4922d2bbd` supplied the reviewed portable boundary.
Its proposed 0.1.0 record was never treated as an observed release. The maintenance implementation was
reviewed in [PR #12](https://github.com/kumwe/computation/pull/12), revalidated and published as unused 0.1.1.
Native main and every prior tag were preserved.

The earlier published 0.2.0 and 0.2.1 artifacts require `ext-kumwe_engine: 0.0.0-dev`; native-backed 0.3.0 also
cannot supply the pre-Engine portable prerequisite. Their historical coordinates remain in the machine
record for audit. No historical development publication was relabelled as a verified portable release.

## Remaining ordered native admission

1. Engine consumes the verified portable baseline and independently verified semantic-owner inputs.
   Frozen ABI compatibility, final native quality and exact candidate cross-build evidence gate Engine release.
2. Independently verify the published Engine source and signed provenance. The PHP extension then embeds
   that exact source, passes its installer/lifecycle/platform/performance gates and publishes its stable release.
3. Independently verify the extension release. Computation selects its exact stable extension version,
   binding source, embedded Engine identity and external release evidence; its CI verifies the actual
   published source bundle and GitHub OIDC provenance before compiling that source.
4. Verify the native-backed Computation successor and downstream SDK release before separately provisioning
   the App and performing its runtime integration. The host still supplies the independent complete build tuple.

`resources/native-adapter.json` distinguishes candidate source from `verified-native-dependencies`.
The latter means the three upstream release prerequisites are verified; it does not self-attest this
Computation successor. Its own release and independent consumer verification follow the complete package gate.

[baseline-attestation]: https://github.com/kumwe/extension-sdk/actions/runs/34250329345/artifacts/10065724495
