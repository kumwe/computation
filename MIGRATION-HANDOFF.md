---
schema: "kumwe-migration-handoff/v2"
artifact_kind: "framework_php"
migration_id: "KUMWE-MIG-2026-008"
change_set: "KUMWE-CS-2026-008"
state: "draft_pr_open"
source:
  app:
    repository: "https://github.com/kumwe/app"
    baseline_commit: "960ce8ec00cf724a7cae03e5ba09c4852c9ab54e"
    examined_paths:
      - "src/BusinessDefinition/Domain"
      - "src/BusinessRecord/Application"
      - "tests/Unit/BusinessDefinition/Domain"
      - "tests/Unit/BusinessRecord/Application"
      - "composer.json"
      - "composer.lock"
      - "docs/architecture/capability-index.md"
    old_namespace_roots: []
    capability_index_sha256: "87ded886f35f74878ca9eb8db4c36e23d681c4a49891f76dfc3210f385a7ce39"
  semantic_inputs:
    - owner: "kumwe/canonical-json"
      version_or_commit: "0.1.1 / e7006a2580a49a1c8ab507b0d7b9c3403b4f9f58"
      manifest_or_corpus: "resources/corpus/v1.json"
      sha256: "84d21b12e7a2bfd752356d9a6e664bcb332e209d19017e7634e7485a4fa4e250"
    - owner: "kumwe/computation"
      version_or_commit: "0.1.1 / fc9d049f8b675c8e19fd1672d49b5e206c9ad52a"
      manifest_or_corpus: "resources/conformance/v1.json (portable transport only)"
      sha256: "30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc"
  examined_dependencies:
    - "php ^8.5"
    - "php-64bit ^8.5"
    - "ext-kumwe_engine 1.0.3"
    - "kumwe/canonical-json 0.1.1"
    - "psr/container ^2.0"
  active_related_pull_requests:
    - "https://github.com/kumwe/engine/pull/14"
    - "https://github.com/kumwe/kumwe-engine/pull/9"
target:
  repository: "https://github.com/kumwe/computation"
  artifact_identity: "kumwe/computation"
  canonical_namespace_or_abi: "Kumwe\\Computation"
  branch: "agent/computation-0.3.3"
  pull_request: null
ownership:
  responsibility: "Portable transport, native adapters, compatibility and deterministic refusals."
  non_responsibilities:
    - "Host trust and final authorization"
    - "Persistence, durable transactions, worker and transport lifecycle"
    - "App runtime adoption and native release publication"
  allowed_dependency_ceiling:
    - "php"
    - "php-64bit"
    - "ext-kumwe_engine"
    - "kumwe/canonical-json"
    - "psr/container"
  implementation_owner: "kumwe/computation"
  next_consumer: "kumwe/extension-sdk"
  public_manifests:
    -
      path: "resources/public-api/v1.json"
      sha256: "c56fc71c10bf8873bdf611fbc0f08c672666df9b5fb315e269f5c706e24a6a9f"
    -
      path: "resources/capabilities/v1.json"
      sha256: "4f674158de34356fabcbf52c55cbc8fb436e320e66574f74d59ab6d9e86f03bb"
    -
      path: "resources/service-map/v1.json"
      sha256: "0c78df81249bde363bcacf7aa14c8d1a8223692bc879f91a65f08c2112dd278d"
    -
      path: "resources/contract-baseline/v1.json"
      sha256: "e17f8ab0c321cb39f3c83d01d33087155fc5af24b20245cb3356ee01fb324eb8"
    -
      path: "resources/native-adapter.json"
      sha256: "f49e1ac62ba3edcd92f9bee2760f8e054788758b1a300cb68c8da57fd39c8d28"
    -
      path: "resources/native-ownership/v1.json"
      sha256: "28756daf1685eddfc2f664c0bd665d77d755029aa4e8238f50a9417efa57396f"
  intentionally_excluded:
    - "Engine owns algorithms; extension owns C ABI/Zend binding."
    - "No App extraction or runtime adoption occurs in this successor."
framework_php:
  composer_package: "kumwe/computation"
  canonical_namespace: "Kumwe\\Computation"
  public_api_manifest: "resources/public-api/v1.json"
  capability_manifest: "resources/capabilities/v1.json"
  service_map: "resources/service-map/v1.json"
  extracted_symbols: []
  consumers:
    app_code: []
    configuration_and_di: []
    reflection_and_string_references:
      - "Re-scan current App dynamic, reflected and same-namespace references before adoption."
    fixtures_and_examples:
      - "examples/typed-consumer.php"
    external:
      - "kumwe/extension-sdk: development/template dependency and explicitly provisioned native CLI."
  dependency_injection:
    mode: "config-provider"
    provider: "Kumwe\\Computation\\ConfigProvider"
    factories:
      - "Kumwe\\Computation\\NativeAdapterFactory"
      - "Kumwe\\Computation\\NativeCanonicalEncoderFactory"
    aliases:
      - "Kumwe\\Computation\\Compiler -> Kumwe\\Computation\\NativeAdapter"
      - "Kumwe\\Computation\\Executor -> Kumwe\\Computation\\NativeAdapter"
    service_lifetimes:
      - "Kumwe\\Computation\\NativeAdapter: shared"
      - "Kumwe\\Computation\\NativeCanonicalEncoder: shared"
    configuration_keys: []
    provider_absence_reason: null
native_cpp: null
php_extension: null
tests:
  moved_or_added:
    - "tests/native-absence.php"
    - "tests/native.php"
    - "tests/run.php"
  remain_in_app_or_consumer:
    - "Retain host composition, authority, persistence and integration assertions in App."
  split_tests:
    - "Remove vendor implementation assertions only after verified App adoption."
  prohibited_duplicates:
    - "App must not duplicate package implementation unit tests after adoption."
  corpora:
    - "resources/conformance/v1.json"
documentation:
  charter: "CHARTER.md"
  readme: "README.md"
  public_api: "docs/public-api.md"
  architecture: "docs/architecture.md"
  integration_or_consumer: "docs/integration.md"
  examples:
    - "examples/typed-consumer.php"
  changelog_record: "CHANGELOG.md / 0.3.3"
release_expectations:
  version_policy: "Exact stable sibling pins; promote compatible published successors together."
  expected_artifact_types:
    - "Composer package archive"
    - "GitHub source archive"
  required_checks:
    - "composer check"
    - "Final hosted package CI"
    - "Machine handoff and consumer schema validation"
  required_registry_or_installer: "Composer"
  required_external_attestation: true
next_task:
  phase_name: "Qualify SDK native development/CLI inputs, then separately integrate into App"
  permitted_only_when:
    - "The extension-free baseline is independently verified before Engine stable admission"
    - "Final package CI passes at the proposed head"
    - "Immutable package and all dependency releases are independently verified"
    - "Reconcile current App drift against the recorded source inventories"
  consumer_repository: "https://github.com/kumwe/extension-sdk"
  dependency_or_native_change: "Select the verified native successor in SDK development, templates and CLI evidence."
  namespace_or_api_replacements: []
  files_to_update:
    - "composer.json"
    - "composer.lock"
    - "resources/extension-scaffold/complete-component/composer.json.tpl"
    - "resources/source-ci-dependencies.json"
    - "resources/source-candidate-dependencies.json"
  files_to_remove: []
  tests_to_remove: []
  tests_to_retain_or_add:
    - "SDK archive and generated no-dev consumers; development native CLI and complete tuple gates."
  di_or_provisioning_changes:
    - "Retain explicit native CLI composition and independent tuple; production uses only CanonicalEncoder."
  capability_index_changes:
    - "Record verified package/native ownership in SDK evidence; App index changes belong to later integration."
  changelog_and_evidence_changes:
    - "Record exact artifact and dependency identities in the external release attestation."
  verification_commands:
    - "composer check"
    - "SDK complete package, archive and generated production consumer gates"
    - "Independent SDK release and all31 consumer verification"
concurrency:
  likely_conflict_files:
    - "App composer.json"
    - "App composer.lock"
    - "App provider configuration"
  related_migrations: []
  ownership_conflicts: []
  integration_train: null
  resolution_rule: "semantic-preservation"
governance:
  roadmap_source_sha256: "a202155ef1a65f5ab293d4f8397ebf4ac430db7f1e877c776bbe7851e6fe18d8"
  roadmap_refs: []
  non_roadmap_refs:
    - "NRM-2026-008"
  completion_claim: false
decisions:
  - "Release compiled plans explicitly to reclaim bounded native capacity."
  - "Engine owns algorithms; Computation owns transport and native adapters."
  - "No App changes or release publication occurs in this successor."
  - "Require opaque-compiled-results/1 at composition; preserve raw native result bytes."
  - "Published portable Computation 0.1.1 is independently verified and preserved byte for byte."
blockers:
  - "Reporting independent semantic-owner release verification remains outstanding upstream of Engine."
  - "Independent Computation successor release verification follows publication."
  - "Final package gates and independent immutable release verification remain required."
  - "Host-specific build tuple must match the provisioned stable extension exactly."
---

# computation implementation handoff

## Migration/implementation summary

Qualify the preserved native adapters using ordered independently verified dependency records and signed
published source bundles. The portable Computation 0.1.1 baseline is now verified; all 22 corresponding
source/corpus files remain unchanged. This correction selects the full-schema-validated durable portable
receipt. Version 0.3.3 selects published native Engine/binding 1.0.3 and verifies their signed source bundles.
No publication or independent Computation release attestation is claimed by this source change.

## Public API and responsibility

Portable transport and native adapters are implemented. Engine owns all algorithms; the PHP extension owns
ABI/Zend binding. The required extension is published 1.0.3; source CI checks both owners
through exact GitHub OIDC provenance, with the complete host build tuple checked separately. Every exported member is
recorded in
resources/public-api/v1.json and documented in docs/public-api.md. The current surface contains 26 types. 0
types have recorded extraction provenance; package-native composition is identified separately.

## Capability reuse/semantic input review

The implementation consumes the exact canonical dependency contracts in composer.json. The independently
verified portable Computation 0.1.1 baseline supplies the pre-Engine prerequisite. Verify Engine stable,
extension stable and this native-backed successor in that order; candidate build receipts alone do not
qualify a release. External evidence and preserved source hashes are in resources/contract-baseline/v1.json.

## Consumer inventory

The machine record lists actual source mappings, known consumer paths and concrete namespace replacements. No
App code is moved by this successor; resources/native-ownership/v1.json and resources/conformance/v1.json
record the native/transport boundary. Dynamic references and same-namespace names must be searched again
during adoption; the inventory does not imply that App has already switched ownership.

## Test ownership

Package tests own portable values, validation, service behavior, explicit construction and malformed-input
regressions. The machine record identifies the source suites to split. Host persistence, transactions,
authority, transport and operational integration stay in App. After verified adoption, remove duplicate
library implementation assertions from App together with their legacy source.

## Next-task execution notes

The published Engine and extension identities are recorded in this successor.
Run the complete native package gate and publish recorded successor 0.3.3;
independent released-artifact verification follows. Only then update SDK's native development/template pins.
App provisioning and runtime integration remain a separate later task with its own current source inventory.

## Drift check

Reconcile the recorded source commit and per-file source digests with the current App before adoption.
Recompute all public manifest hashes together. Keep actual release observations and final tested commit
identities outside the tested source tree to avoid self-referential evidence.

## Validation recipe and observed local results

Run composer check with the documented PHP runtime and extensions. The review added and exercised the boundary
regressions described in CHANGELOG.md. A final gate pass, remote CI status and immutable release verification
are distinct observations; neither a proposed version nor this handoff attests publication. See
docs/integration.md and the package check scripts for the exact archive and runtime recipe.
