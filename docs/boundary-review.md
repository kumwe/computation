# Independent boundary review

The root implementation coordinator independently reviewed the initial contract proposal at commit
`bcc5fc60e596664a08440b9cd8aa670d03c99274` before production source implementation began.
App source baseline: `960ce8ec00cf724a7cae03e5ba09c4852c9ab54e`.

The review approved the independent Phase 1A transport boundary, with seven required corrections:

1. All nonzero C ABI statuses return null; success returns a non-null buffer.
2. Define the private typed length-prefix identity grammar separately from future native ABI framing.
3. Validate source/profile/version agreement at compilation and the exact artifact tuple at hydration.
4. Validate batch result count/order/correlation and semantic identity against the expected request.
5. Count finding/path/parameter bytes in aggregate limits; enforce hard and caller limits before decoding.
6. Validate UTF-8 and byte limits, detach PHP references and reject hostile nested shapes.
7. Give execution budgets finite ceilings without claiming a PHP clock/cancellation implementation.

These corrections were incorporated into the draft before source implementation. Package tests must
cover their executable invariants. This is an independent architectural review, not a human approval,
native ABI freeze, native implementation check, release attestation or App readiness claim.

No semantic dependency or corpus was silently omitted: the baseline owns metadata and opaque transport
only. It does not select a Conversion/Canonical JSON execution profile. The current native adapter now selects the
released canonical contract and exact candidate profiles.
Independent immutable native release verification remains required.
