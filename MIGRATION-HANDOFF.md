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
  pull_request: "https://github.com/kumwe/computation/pull/12"
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
    mode: "direct"
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
  version_policy: "0.1.1 portable maintenance proposal; no semantic dependencies or publication claim."
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
  phase_name: "Phase 1B native-binding successor after verified baseline, Engine and extension releases"
  permitted_only_when:
    - "The extension-free 0.1 maintenance release is published and independently verified."
    - "Engine and kumwe-engine stable releases and exact compatibility tuple are independently verified."
    - "Reconcile current native main against the exact released portable API and corpus without semantic duplication."
  consumer_repository: "https://github.com/kumwe/computation"
  dependency_or_native_change: "Qualify the preserved native adapter against actual stable Engine/extension releases."
  namespace_or_api_replacements: []
  files_to_update:
    - "composer.json"
    - "src/NativeCompatibility.php"
    - "resources/native-adapter.json"
    - "resources/native-ownership/v1.json"
    - "resources/public-api/v1.json"
    - "resources/capabilities/v1.json"
    - "resources/service-map/v1.json"
    - "MIGRATION-HANDOFF.md"
  files_to_remove: []
  tests_to_remove: []
  tests_to_retain_or_add:
    - "Retain portable behavior/boundary/conformance tests and tests/ownership.json."
    - "Qualify preserved native adapter, factory, explicit tuple and actual extension archive tests."
  di_or_provisioning_changes:
    - "Baseline has no provider; the successor qualifies explicit native factories after stable tuple verification."
  capability_index_changes:
    - "Update package native capabilities; App capability-index work belongs to the later App integration."
  changelog_and_evidence_changes:
    - "Record baseline and native release attestations externally; propose the reviewed native successor record."
  verification_commands:
    - "composer check"
    - "Actual native compatibility and archive-consumer gates against the verified extension tuple"
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

## Migration/implementation summary

[PR #12](https://github.com/kumwe/computation/pull/12) reconstructs **Phase 1A contract_baseline** as proposed
maintenance 0.1.1. It preserves native main and every existing tag. The archive requires only 64-bit PHP 8.5:
no extension, native adapter, provider, Composer sibling dependency or PHP algorithm/fallback is supplied.

## Public API and responsibility

The 20 public types own execution identity, limits, opaque program/document/result transport, ordered findings,
refusals and coarse Compiler/Executor contracts. Their complete surface is in [public API](docs/public-api.md)
and resources/public-api/v1.json; capabilities and explicit provider absence are recorded in the other public
manifests. The exact native FQCNs remain exclusively extension-owned under resources/native-ownership/v1.json.

## Capability reuse/semantic input review

The 20 public source files, Internal/Guard.php and transport corpus are byte-identical to reviewed native source
7bff8d3c916612d374c63e56ef3f2ef76e898322. No runtime semantic input was selected: opaque transport does not
implement Conversion, Definition, Reporting or Canonical JSON semantics. The existing native ABI reservation,
including explicit plan release, records examined development ownership only and is not a runtime dependency.
The historical source 3520a11a4acb075562db9dea0d23fbd4922d2bbd remains reconstruction evidence, not a release.

## Consumer inventory

This baseline extracts no existing App class. docs/consumer-inventory.json records zero immediate App source,
configuration, DI or test removals and identifies Engine as the first manifest/corpus consumer. The original
App source baseline is historical evidence; it must be refreshed during later App adoption. Composer supplies
only the portable namespace. No native class is autoloaded and no App configuration changes belong here.

## Test ownership

All exports retain concrete behavior and boundary mappings in tests/ownership.json. The transport-only corpus
remains package-owned conformance evidence. Fourteen portable tests execute 532 assertions; the no-dev archive
consumer replays all of them with php -n against the dependency's own classes and shipped corpus. API, boundary,
ownership, static analysis and hostile-input gates remain mandatory. Native tests remain on native main, while
App authority, persistence, transactions, provisioning and integration tests remain with App.

## Next-task execution notes

Human merge into maintenance/portable-contracts triggers the identical complete package gate and exact merged
source release. The committed policy admits this repository, this branch and only 0.1 patches from 0.1.1 onward;
it cannot manufacture missing historical 0.1.0 or publish native-series versions. Independently verify actual
publication, Composer/archive identity, manifests, corpus and shipped handoff externally before admitting the
baseline to Engine's release gate. Engine and extension stable releases must then be independently verified.

Only then qualify Phase 1B on the preserved native development line. Reconcile its portable API/corpus with this
release; replace development compatibility coordinates using actual stable native identities; retain semantics-
free adapters, explicit factories and absence/hostile/clean-consumer tests. Update the exact files in next_task.
The Phase 1B handoff points to the separately authorized App integration. No App implementation belongs here.

## Drift check

Compare every portable exported signature and corpus byte against the independently verified baseline before
changing native main. Review new package/native changes against their own source and handoffs, and route any
portable contract correction through its own reviewed successor. Refresh App inventories only during later
adoption; never delete newer App behavior using this historical inventory. Refresh all handoff manifest hashes
together. Exact tested source/archive identities remain external to avoid self-referential evidence.

## Validation recipe and observed local results

Run composer check on PHP 8.5 without kumwe_engine and run the four release regression scripts in CI. Local
portable tests pass 14 groups/532 assertions with and without INI; the 50-file no-dev authoritative archive
passes the same 532 assertions, source-location guard, 20-class smoke and shipped example. PHPStan, coding,
API/architecture and ownership gates passed. Release parser, integrity and publication regression suites passed
12, 92 and 58 cases respectively. Final hosted Package gate and actual publication remain separate observations;
this handoff contains no release attestation or future source/archive identity.
