# Computation boundary invariants

Package tests enforce these portable and native adapter invariants:

1. Nonzero C ABI statuses return no successful result; owned success buffers require explicit cleanup.
2. The private typed length-prefix identity grammar remains separate from native ABI framing.
3. Compilation validates source/profile/version agreement; hydration validates the complete artifact tuple.
4. Batch results preserve expected count, order, correlation and semantic identity.
5. Finding, path and parameter bytes count toward aggregate limits before decoding.
6. Transport validates UTF-8 and byte limits, detaches PHP references and rejects hostile nested shapes.
7. Execution budgets have finite ceilings; metadata does not implement a PHP clock or cancellation engine.

The portable baseline owns metadata and opaque transport. Native adapters select the released canonical contract
and exact native profiles, while Engine retains semantic execution. Review new boundary changes against these
invariants, the [public API](public-api.md) and [Core contract](core-contract.md).

Package tests, independent release verification and Core workload acceptance are separate evidence.
