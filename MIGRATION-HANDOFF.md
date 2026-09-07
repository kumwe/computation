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
  semantic_inputs: []
  examined_dependencies:
    - "php ^8.5"
    - "php-64bit ^8.5"
    - "ext-kumwe_engine 0.0.0-dev"
    - "kumwe/canonical-json 0.1.1"
    - "psr/container ^2.0"
  active_related_pull_requests: []
target:
  repository: "https://github.com/kumwe/computation"
  artifact_identity: "kumwe/computation"
  canonical_namespace_or_abi: "Kumwe\\Computation"
  branch: "codex/extraction-readiness-20260907"
  pull_request: "https://github.com/kumwe/computation/pull/11"
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
  next_consumer: "kumwe/app"
  public_manifests:
    -
      path: "resources/public-api/v1.json"
      sha256: "cd1a3e6abe265e043fc2a245f12a3fdddb5af95fb8762b6a34dc7de12578f97a"
    -
      path: "resources/capabilities/v1.json"
      sha256: "a7034c5b068d17f07e7f983e0c32bbf72069841b5643e59193a433cabbdb414d"
    -
      path: "resources/service-map/v1.json"
      sha256: "2f83cffac717f24e7911dfd7860657342bf19f1736a5d9f0b2b74f67f81a4dfd"
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
    external: []
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
  changelog_record: "CHANGELOG.md / 0.2.2"
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
  phase_name: "Review and verify the library successor release before separate App integration"
  permitted_only_when:
    - "Final package CI passes at the proposed head"
    - "Immutable package and all dependency releases are independently verified"
    - "Reconcile current App drift against the recorded source inventories"
  consumer_repository: "https://github.com/kumwe/app"
  dependency_or_native_change: "Require verified Engine/extension releases and exact tuple; see docs/integration.md."
  namespace_or_api_replacements: []
  files_to_update:
    - "composer.json"
    - "composer.lock"
  files_to_remove: []
  tests_to_remove:
    - "Portable implementation assertions only, after App integration gates pass."
  tests_to_retain_or_add:
    - "Host composition and operational integration suites."
  di_or_provisioning_changes:
    - "Bind NativeCompatibility and package factories; host selects CanonicalEncoder binding."
  capability_index_changes:
    - "Record verified package capability and API ownership."
  changelog_and_evidence_changes:
    - "Record exact artifact and dependency identities in the external release attestation."
  verification_commands:
    - "composer check"
    - "Affected App integration suites"
    - "Complete App package governance gate"
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
blockers:
  - "Final package gates and independent immutable release verification remain required."
  - "Engine/extension remain development candidates; no verified stable tuple exists."
---

# computation implementation handoff

## Migration/implementation summary

Expose explicit compiled-plan release to reclaim native capacity in long-lived consumers and normalize
consumer manifests. [PR #11](https://github.com/kumwe/computation/pull/11) contains this successor. The
changelog version describes the proposed artifact; it is not a publication observation.

## Public API and responsibility

Portable transport and native adapters are implemented. Engine owns all algorithms; the PHP extension owns
ABI/Zend binding. The available 0.0.0-dev native tuple remains a development candidate, so independent stable
native release verification still gates App adoption. Every exported member is recorded in
resources/public-api/v1.json and documented in docs/public-api.md. The current surface contains 26 types. 0
types have recorded extraction provenance; package-native composition is identified separately.

## Capability reuse/semantic input review

The implementation consumes the exact canonical dependency contracts recorded in composer.json. Provision
independently verified Engine and PHP-extension releases, select their exact ABI/profile/corpus tuple and
provide NativeCompatibility before installing the successor. The tested 0.0.0-dev extension is candidate
evidence only.

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

Independent successor release verification and the final package gate remain necessary before App adoption.
Native extension and Engine are development candidates; no verified stable native release tuple exists.
Provision independently verified Engine and PHP-extension releases, select their exact ABI/profile/corpus
tuple and provide NativeCompatibility before installing the successor. The tested 0.0.0-dev extension is
candidate evidence only. Run final source and clean archive gates before admitting the package; then update
the App dependency lock, replace namespaces, retain host adapters and remove only the inventoried portable
legacy implementations.

## Drift check

Reconcile the recorded source commit and per-file source digests with the current App before adoption.
Recompute all public manifest hashes together. Keep actual release observations and final tested commit
identities outside the tested source tree to avoid self-referential evidence.

## Validation recipe and observed local results

Run composer check with the documented PHP runtime and extensions. The review added and exercised the boundary
regressions described in CHANGELOG.md. A final gate pass, remote CI status and immutable release verification
are distinct observations; neither a proposed version nor this handoff attests publication. See
docs/integration.md and the package check scripts for the exact archive and runtime recipe.
