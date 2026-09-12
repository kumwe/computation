---
schema: kumwe-package-release-record/v1
artifact_kind: framework_php
migration_id: KUMWE-MIG-2026-008
change_set: KUMWE-CS-2026-008
source:
  app:
    repository: https://github.com/kumwe/app
    baseline_commit: 960ce8ec00cf724a7cae03e5ba09c4852c9ab54e
    examined_paths:
    - src/BusinessDefinition/Domain
    - src/BusinessRecord/Application
    - tests/Unit/BusinessDefinition/Domain
    - tests/Unit/BusinessRecord/Application
    - composer.json
    - composer.lock
    - docs/architecture/capability-index.md
    old_namespace_roots: []
    capability_index_sha256: 87ded886f35f74878ca9eb8db4c36e23d681c4a49891f76dfc3210f385a7ce39
  semantic_inputs:
  - owner: kumwe/canonical-json
    version_or_commit: 0.1.1 / e7006a2580a49a1c8ab507b0d7b9c3403b4f9f58
    manifest_or_corpus: resources/corpus/v1.json
    sha256: 84d21b12e7a2bfd752356d9a6e664bcb332e209d19017e7634e7485a4fa4e250
  - owner: kumwe/computation
    version_or_commit: 0.1.1 / fc9d049f8b675c8e19fd1672d49b5e206c9ad52a
    manifest_or_corpus: resources/conformance/v1.json (portable transport only)
    sha256: 30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc
  examined_dependencies:
  - php ^8.5
  - php-64bit ^8.5
  - ext-kumwe_engine 1.0.3
  - kumwe/canonical-json 0.1.1
  - psr/container ^2.0
target:
  repository: https://github.com/kumwe/computation
  artifact_identity: kumwe/computation
  canonical_namespace_or_abi: Kumwe\Computation
ownership:
  responsibility: Portable transport, native adapters, compatibility and deterministic refusals.
  non_responsibilities:
  - Host trust and final authorization
  - Persistence, durable transactions, worker and transport lifecycle
  - App runtime adoption and native release publication
  allowed_dependency_ceiling:
  - php
  - php-64bit
  - ext-kumwe_engine
  - kumwe/canonical-json
  - psr/container
  implementation_owner: kumwe/computation
  next_consumer: kumwe/extension-sdk
  public_manifests:
  - path: resources/public-api/v1.json
    sha256: c56fc71c10bf8873bdf611fbc0f08c672666df9b5fb315e269f5c706e24a6a9f
  - path: resources/capabilities/v1.json
    sha256: 4f674158de34356fabcbf52c55cbc8fb436e320e66574f74d59ab6d9e86f03bb
  - path: resources/service-map/v1.json
    sha256: 0c78df81249bde363bcacf7aa14c8d1a8223692bc879f91a65f08c2112dd278d
  - path: resources/contract-baseline/v1.json
    sha256: e17f8ab0c321cb39f3c83d01d33087155fc5af24b20245cb3356ee01fb324eb8
  - path: resources/native-adapter.json
    sha256: f49e1ac62ba3edcd92f9bee2760f8e054788758b1a300cb68c8da57fd39c8d28
  - path: resources/native-ownership/v1.json
    sha256: 28756daf1685eddfc2f664c0bd665d77d755029aa4e8238f50a9417efa57396f
  intentionally_excluded:
  - Engine owns algorithms; extension owns Zend binding and native allocation.
  - Core owns runtime composition, authority, storage and provisioning.
framework_php:
  composer_package: kumwe/computation
  canonical_namespace: Kumwe\Computation
  public_api_manifest: resources/public-api/v1.json
  capability_manifest: resources/capabilities/v1.json
  service_map: resources/service-map/v1.json
  extracted_symbols: []
  consumers:
    app_code: []
    configuration_and_di: []
    reflection_and_string_references:
    - Re-scan current App dynamic, reflected and same-namespace references before adoption.
    fixtures_and_examples:
    - examples/typed-consumer.php
    external:
    - 'kumwe/extension-sdk: development/template dependency and explicitly provisioned native CLI.'
  dependency_injection:
    mode: config-provider
    provider: Kumwe\Computation\ConfigProvider
    factories:
    - Kumwe\Computation\NativeAdapterFactory
    - Kumwe\Computation\NativeCanonicalEncoderFactory
    aliases:
    - Kumwe\Computation\Compiler -> Kumwe\Computation\NativeAdapter
    - Kumwe\Computation\Executor -> Kumwe\Computation\NativeAdapter
    service_lifetimes:
    - 'Kumwe\Computation\NativeAdapter: shared'
    - 'Kumwe\Computation\NativeCanonicalEncoder: shared'
    configuration_keys: []
    provider_absence_reason: null
native_cpp: null
php_extension: null
tests:
  moved_or_added:
  - tests/native-absence.php
  - tests/native.php
  - tests/run.php
  remain_in_app_or_consumer:
  - Retain host composition, authority, persistence and integration assertions in App.
  split_tests:
  - Remove vendor implementation assertions only after verified App adoption.
  prohibited_duplicates:
  - App must not duplicate package implementation unit tests after adoption.
  corpora:
  - resources/conformance/v1.json
documentation:
  charter: CHARTER.md
  readme: README.md
  public_api: docs/public-api.md
  architecture: docs/architecture.md
  integration_or_consumer: docs/integration.md
  examples:
  - examples/typed-consumer.php
  changelog_record: CHANGELOG.md / 0.3.3
release_expectations:
  version_policy: Exact stable sibling pins; promote compatible published successors together.
  expected_artifact_types:
  - Composer package archive
  - GitHub source archive
  required_checks:
  - composer check
  - Final hosted package CI
  - Package release-record and consumer schema validation
  required_registry_or_installer: Composer
  required_external_attestation: true
governance:
  completion_claim: false
decisions:
- Release compiled plans explicitly to reclaim bounded native capacity.
- Engine owns algorithms; Computation owns transport and native adapters.
- Require opaque-compiled-results/1 at composition; preserve raw native result bytes.
- Published portable Computation 0.1.1 is independently verified and preserved byte for byte.
blockers: []
consumer_contract:
  permitted_only_when:
  - Verify the published package, exact native artifacts and independently derived host build tuple.
  consumer_repository: https://github.com/kumwe/extension-sdk
  dependency_or_native_change: Select the verified package and provision its exact native dependency tuple before
    use.
  namespace_or_api_replacements: []
  files_to_update: []
  files_to_remove: []
  tests_to_remove: []
  tests_to_retain_or_add:
  - SDK archive and generated no-dev consumers; development native CLI and complete tuple gates.
  di_or_provisioning_changes:
  - Retain explicit native CLI composition and independent tuple; production uses only CanonicalEncoder.
  capability_index_changes:
  - Core maintains its live dependency and capability inventory.
  changelog_and_evidence_changes:
  - Record exact artifact and dependency identities in the external release attestation.
  verification_commands:
  - composer check
  - composer clean-consumer
---

# Computation release record

## Package contract

Computation owns bounded transport, compatibility, native adapters and explicit PHP composition. Engine owns
algorithms; the binding owns Zend transport. The [Core contract](core-contract.md) defines the host boundary.
Retained migration/change-set IDs identify existing independent attestations and are not pending extraction work.

## Public API and responsibility

The [public API](public-api.md) and public manifests define 20 portable transport types and six native
adapter/composition types. Inputs and semantic results remain opaque; the host selects the complete native tuple.

## Dependencies and semantic inputs

[Composer metadata](../composer.json) pins the native extension and canonical contract. The verified portable
0.1.1 baseline and all 22 preserved source/corpus files remain bound by
[baseline evidence](contract-baseline.md). The current native dependency pair is Engine and binding 1.0.3.

## Consumer contract

Consumers supply NativeCompatibility independently, apply ConfigProvider in a request-scoped container, select
their canonical encoder and release finished plans. Core owns authority, transactions, storage and provisioning.
See [integration](integration.md) for the complete composition and refusal requirements.

## Test ownership

Computation owns transport, validation, service behavior, bounds, identities and malformed-input regressions.
Native semantics and Zend lifetimes retain their respective owners. Core retains integration and recovery tests.
[Ownership documentation](test-ownership.md) explains the source-baseline evidence and current responsibilities.

## Consumer verification

Run the complete package gate with the admitted extension and independently configured expected tuple. Verify
published source, registry identity, manifests, archive and clean consumer before deployment. Publication does
not establish Core adoption, capacity or independent consumer acceptance.

## Compatibility and drift

Keep public manifest digests synchronized and preserve the verified portable source/corpus closure. Exact native
artifact, corpus, capability and build identities must agree. External records identify final published commits
and archive digests; the tested source never invents its own future identity.

## Validation

Run composer check with KUMWE_NATIVE_EXPECTED_TUPLE set to the independently derived compatibility JSON.
The shared PR and release workflow executes the same package, native and no-dev consumer gates. Evidence applies
to its exact tested commit and supported host tuple. See [releasing](releasing.md).
