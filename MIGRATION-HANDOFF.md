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
    - "CanonicalEncoder GenericV1 port; exact native corpus selected by host NativeCompatibility."
    - "App retains existing Expression, DecimalValue and document validation implementations."
    - "Native extension, canonical port and PSR container are explicit runtime dependencies."
  active_related_pull_requests:
    - "https://github.com/kumwe/app/pull/135"
target:
  repository: "https://github.com/kumwe/computation"
  artifact_identity: "kumwe/computation"
  canonical_namespace_or_abi: "Kumwe\\Computation"
  branch: "codex/native-adapter-candidate"
  pull_request: "https://github.com/kumwe/computation/pull/7"
ownership:
  responsibility: "Portable bounded execution transport, exact identities and compiler/executor contracts."
  non_responsibilities:
    - "Engine algorithms, decimal/expression semantics, document normalization and canonical JSON."
    - "C ABI implementation, Zend binding, extension provisioning and host container lifetime."
    - "Authority, persistence, transactions, cache lifetime, localization and App adoption."
  allowed_dependency_ceiling:
    - "ext-kumwe_engine"
    - "kumwe/canonical-json"
    - "psr/container"
  implementation_owner: "kumwe/computation"
  next_consumer: "kumwe/engine"
  public_manifests:
    - path: "resources/public-api/v1.json"
      sha256: "66d01e94387496930320075f6340e8ad7c40da04f09026be89aaa94fadda466f"
    - path: "resources/capabilities/v1.json"
      sha256: "f123a7693692f564d18030bc8925672d2dbbc34989015b477b46eabef888e5f0"
    - path: "resources/service-map/v1.json"
      sha256: "dfe0dab4f2b8a502cb2fe1105a6d022b463c9df00ca1315c0c5ce42327401191"
    - path: "resources/native-ownership/v1.json"
      sha256: "35cdddeb2cc2dc1557340ea282b1685da9c857724f9f55d84ca7f293bf6a3b4c"
    - path: "resources/conformance/v1.json"
      sha256: "30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc"
  intentionally_excluded:
    - "No verbatim App extraction or App code/test removal in Phase 1A."
    - "No autoloadable native classes, PHP executor or semantic fallback."
    - "Transport corpus remains synthetic; actual native integration is separately required."
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
    reflection_and_string_references: []
    fixtures_and_examples: []
    external:
      - "Future Engine implementation consumes the independently verified contract baseline."
      - "Future Computation Phase 1B adapter consumes verified native releases."
  dependency_injection:
    mode: "explicit-provider"
    provider: "Kumwe\\Computation\\ConfigProvider"
    factories:
      - "NativeAdapterFactory"
      - "NativeCanonicalEncoderFactory"
    aliases:
      - "Compiler and Executor -> NativeAdapter"
      - "Kumwe\\CanonicalJson\\CanonicalEncoder -> NativeCanonicalEncoder"
    service_lifetimes:
      - "Shared native services within a host-owned request container."
      - "NativeCompatibility is host-supplied; upstream canonical Limits is optional."
    configuration_keys: []
native_cpp: null
php_extension: null
tests:
  moved_or_added:
    - "tests/run.php"
    - "Package-owned contract invariants, wire round trips, identity vectors and hostile bounds."
    - "tools/test-release-record.sh"
    - "tools/verify-clean-consumer.php"
  remain_in_app_or_consumer:
    - "All existing App tests remain during Phase 1A."
    - "App authority, composition, DB/transaction, deployment, stale-generation and recovery coverage."
    - "Engine semantic algorithms, parity corpora, fuzzing, sanitizers and performance coverage."
    - "Native binding Zend lifecycle, cleanup, marshalling, PHPT and installer coverage."
  split_tests:
    - "At native cutover, refresh exact App test methods and separate portable invariants from integration."
    - "Do not delete an App test before its verified replacement implementation is adopted."
  prohibited_duplicates:
    - "Do not duplicate Computation contract unit tests inside App after adoption."
    - "No parallel App or package PHP executor to bypass ordered native cutover."
  corpora:
    - "resources/conformance/v1.json: transport-only vectors, not semantic owner/parity attestations."
documentation:
  charter: "CHARTER.md"
  readme: "README.md"
  public_api: "docs/public-api.md"
  architecture: "docs/architecture.md"
  integration_or_consumer: "docs/integration.md"
  examples:
    - "examples/typed-consumer.php"
  changelog_record: "CHANGELOG.md / 0.2.0 (proposed native adapter candidate)"
release_expectations:
  version_policy: "SemVer; exact pre-1.0 pins; protected main and immutable release enabled before first merge."
  expected_artifact_types:
    - "Composer package ZIP"
    - "GitHub source archive"
  required_checks:
    - "composer check on 64-bit PHP 8.5"
    - "Real built ZIP as no-dev dependency in a fresh authoritative Composer consumer"
    - "Native-boundary ownership review without claiming an implemented/frozen Engine ABI"
    - "Independent release/source/artifact/manifest/Packagist verification"
  required_registry_or_installer: "Packagist + Composer"
  required_external_attestation: true
next_task:
  phase_name: "Verify complete native candidate and independent immutable releases before consumer adoption"
  permitted_only_when:
    - "Human merge and immutable release after main protection and release immutability are enabled."
    - "Independent external attestation verifies the exact artifact and full handoff."
    - "Engine selects exact independently verified semantic owner APIs and corpus digests."
  consumer_repository: "https://github.com/kumwe/engine"
  dependency_or_native_change: "Adopt independently verified native, canonical port and adapter artifacts."
  namespace_or_api_replacements: []
  files_to_update:
    - "Future Engine ABI/header/implementation and semantic dependency records after native review."
    - "Future native binding lifecycle/marshalling/installer and joint ownership records."
  files_to_remove: []
  tests_to_remove: []
  tests_to_retain_or_add:
    - "Keep all App execution tests until actual verified native cutover."
    - "Add Engine algorithm parity, native ABI, fuzz, sanitizer and performance tests."
    - "Add binding Zend lifecycle, resource cleanup, PHPT and installer tests."
  di_or_provisioning_changes:
    - "None in Phase 1A; native provisioning must merge before later App execution cutover."
  capability_index_changes:
    - "None in App Phase 1A; regenerate only after verified dependency adoption."
  changelog_and_evidence_changes:
    - "KUMWE-MIG-2026-008 / KUMWE-CS-2026-008 / NRM-2026-010; no App completion claim."
  verification_commands:
    - "composer check"
    - "Independent exact release/archive/manifest/Packagist verification"
    - "Engine and binding native release gates before Phase 1B or App adoption"
concurrency:
  likely_conflict_files:
    - "Computation public API and native ownership manifests"
    - "Engine ABI headers, semantic input records and implementation plans"
    - "Native binding FQCN ownership, stubs/arginfo and lifecycle plans"
  related_migrations: []
  ownership_conflicts:
    - "Native declarations belong only to Engine binding; Composer must not autoload stubs."
    - "Semantic payload owners retain semantics and release/corpus authority."
  integration_train: null
  resolution_rule: "semantic-preservation"
governance:
  roadmap_source_sha256: "a202155ef1a65f5ab293d4f8397ebf4ac430db7f1e877c776bbe7851e6fe18d8"
  roadmap_refs: []
  non_roadmap_refs:
    - "NRM-2026-010"
  completion_claim: false
decisions:
  - "Native adapter candidate: 26 public PHP types, zero immediate App removals."
  - "CanonicalEncoder is an injected upstream port; the Engine owns semantic execution."
  - "Explicit native services; no PHP semantic fallback or automatic host readiness."
blockers:
  - "Immutable publication and independent attestation are future gates, not completed evidence."
  - "Native layout, platform/lifecycle implementation and semantic parity remain downstream work."
  - "App cutover waits for verified baseline/Engine/binding/adapter releases and prior provisioning merge."
---

# Computation migration handoff

The 0.2.0 candidate adds six public types for native compatibility, execution, canonical encoding and explicit
container composition. Its 26 exports are reflected in the API manifest and five capability groups. App keeps
all existing production code and tests until an independently verified adoption task performs the cutover.

NativeAdapter owns request-local plan handles and makes coarse calls to the actual Zend Runtime. The two native
factories require a host-selected exact NativeCompatibility tuple, including the independently recorded binding build digest that binds PHP patch, Zend API, platform, compiler/flags, debug/sanitizers and ABI manifest. Compiler and Executor resolve to the same
shared adapter. NativeCanonicalEncoder implements the upstream GenericV1 port with native execution only.
The host retains authorization, persistence, transactions, provisioning, readiness and request/container lifetime.

Computation owns transport invariants, metadata identities, factory/compatibility boundaries and actual adapter
integration. Engine owns algorithms and native parity evidence; the binding owns Zend lifecycle and marshalling.
Native integration also exercises Unicode validators, exact normalized domain storage, create/update preparation, converted report output and refusal recovery through the coarse adapter. The transport corpus remains transport-only. The separate native suite must execute against the real extension
and an independently supplied tuple, including through the no-dev archive's authoritative Composer autoloader.

Run `composer check` with the actual extension and `KUMWE_NATIVE_EXPECTED_TUPLE` set to the admitted candidate's
configuration JSON. Absence or mismatch is a required failure. Source/static successes alone cannot establish
native compatibility, release admission or App completion. The PR records observed results; final release and
artifact identities belong in independent external evidence after publication.
