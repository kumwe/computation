# Transport conformance corpus

Profile: `transport-only-v1`. Source: `resources/conformance/v1.json`.

SHA-256: `30a64cf682a46c38ca99544d5abcad43cda7071bb9702621226bf297411b8bfc`

The corpus locks closed metadata encodings, a complete plan/cache identity and stable severity/refusal tokens.
It selects no semantic package release and claims no native implementation or domain algorithm parity.
Package tests exercise every vector; the ownership gate checks scope and verifies this exact digest.

## Native integration requirement

The transport corpus above remains transport-only. Native integration has a separate mandatory executable
suite in `tests/native.php`: actual extension compilation/execution, exact tuple comparison, typed portable
findings, plan ownership, bounded lossless input transport and native canonical bytes/digests. Its expected
compatibility JSON is independently supplied by the host or CI, never copied from a live handshake.
`tests/native-absence.php` proves the factory refuses absent native code without invoking autoload.
Neither test substitutes a PHP semantic implementation for the Engine. A missing native build fails the full
gate; successful metadata checks alone cannot establish native conformance or release admission.
