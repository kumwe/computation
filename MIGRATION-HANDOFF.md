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
  active_related_pull_requests: []
target:
  repository: "https://github.com/kumwe/computation"
  artifact_identity: "kumwe/computation"
  canonical_namespace_or_abi: "Kumwe\\Computation"
  branch: "codex/portable-contract-baseline"
  pull_request: null
ownership:
  responsibility: "Phase 1A portable execution transport, compatibility and deterministic refusals."
  non_responsibilities:
    - "Host trust and final authorization"
    - "Persistence, durable transactions, worker and transport lifecycle"
    - "App runtime adoption and native release publication"
  allowed_dependency_ceiling:
    - "php"
    - "php-64bit"
  implementation_owner: "kumwe/computation"
  next_consumer: "kumwe/engine"
  public_manifests:
    -
      path: "resources/public-api/v1.json"
      sha256: "bb0a1630682953413acc20a540471b9308cf14c62cdb9162d5fa1bd5b55af9a4"
    -
      path: "resources/capabilities/v1.json"
      sha256: "b0b5a5df7f527685a3f069c3f1ff8418dbb29f51850ee074d2ab849e834ae7e7"
    -
      path: "resources/service-map/v1.json"
      sha256: "63d04ed230e1f70b88910aa27b11f09e50458d8d8a9722c5f30feb6954fb9fce"
    -
      path: "resources/contract-baseline/v1.json"
      sha256: "7067acda3b4bcb412e887d7c9711efe0257573530b874a3e48302717e07b9499"
  intentionally_excluded:
    - "Engine owns algorithms; extension owns C ABI/Zend binding."
    - "No App extraction, native runtime binding or runtime adoption occurs in this baseline."
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
    mode: "none"
    provider: null
    factories: []
    aliases: []
    service_lifetimes: []
    configuration_keys: []
    provider_absence_reason: "Direct immutable portable contracts; no runtime implementation or native binding."
native_cpp: null
php_extension: null
tests:
  moved_or_added:
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
  changelog_record: "CHANGELOG.md / 0.1.1"
release_expectations:
  version_policy: "The reviewed 0.1.1 portable maintenance record is not a publication observation; no semantic dependency is selected."
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
  phase_name: "Independently verify Phase 1A baseline, then admit Engine candidate release"
  permitted_only_when:
    - "The extension-free baseline is independently verified before Engine stable admission"
    - "Final package CI passes at the proposed head"
    - "Immutable package and all dependency releases are independently verified"
    - "Reconcile current App drift against the recorded source inventories"
  consumer_repository: "https://github.com/kumwe/engine"
  dependency_or_native_change: "Record exact verified baseline API/corpus in Engine; no App integration in Phase 1A."
  namespace_or_api_replacements: []
  files_to_update:
    - "resources/contracts.json"
    - "docs/releasing.md"
  files_to_remove: []
  tests_to_remove:
    - "Portable implementation assertions only, after App integration gates pass."
  tests_to_retain_or_add:
    - "Host composition and operational integration suites."
  di_or_provisioning_changes:
    - "None in Phase 1A; portable contracts have no service provider."
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
  - "Reconstruct Phase 1A on maintenance/portable-contracts and preserve native main and every existing tag."
  - "Keep the 20 public portable source files, Internal/Guard and transport corpus identical to reviewed native main."
  - "Select no runtime semantic dependency; no native adapter, provider, native requirement or algorithm is shipped."
  - "The 0.1.1 record selects a future release; verified_release and external attestation remain null."
blockers:
  - "Maintainer review/merge and actual portable publication are pending."
  - "Independent released-artifact verification must precede native release admission."
  - "Engine/extension remain development candidates; Phase 1B qualification waits for their verified stable releases."
---

# computation portable contract baseline handoff

This is the reconstructed **Phase 1A contract_baseline**, proposed as maintenance 0.1.1. It preserves native
main and historical releases. It requires only 64-bit PHP 8.5: no extension, native service, semantic algorithm,
Composer sibling dependency or PHP fallback. Its 20 portable public source files and Internal/Guard.php are
identical to the reviewed native source 7bff8d3c916612d374c63e56ef3f2ef76e898322, as is the transport corpus.

The package owns identity, bounds, opaque program/document/result transport, ordered findings, refusals and
coarse Compiler/Executor contracts. It extracts no existing App class. docs/consumer-inventory.json records
zero immediate App removals; the original App source baseline remains historical extraction evidence only.
Native ABI classes remain extension-owned, as documented in resources/native-ownership/v1.json. The historical
native candidate coordinate records examined ownership, not a runtime dependency or released artifact.

All exported types retain concrete behavior/boundary mappings. The transport-only corpus and its fixed digest
remain package-owned conformance evidence. Composer check includes API/architecture/ownership/static/security
checks, complete portable tests and a fresh archive installed with no-dev authoritative loading. CI also runs
the portable suite with php -n. Native suites remain on native main and belong to the later qualified successor.

Human merge into maintenance/portable-contracts starts its complete release-on-record gate. The committed
portable-release policy confines this exception to this repository, that branch and the 0.1 patch line beginning
at 0.1.1. It cannot manufacture the absent historical 0.1.0 release or publish native series versions. Existing
release identities remain immutable in workflow behavior, regardless of optional platform hardening settings.

Publication alone establishes package-released. A separate verification must record the observed tag/commit,
Composer/archive identity, shipped handoff and exact API/capability/service/corpus hashes in external evidence.
Only that verified baseline can clear Engine's ordered prerequisite. Engine and extension stable releases must
then be independently verified before the native Computation successor qualifies. App integration is excluded.

See docs/contract-baseline.md, docs/releasing.md and docs/integration.md for the exact follow-on obligations.
This handoff records expected checks, never its own final source or archive digest and never a release attestation.
