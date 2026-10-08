# Current Acceptance status

`docs/history/bundle-gaps.json` is the reviewed inventory. Only active failing cases carry `#[Group('bundle-gap')]` and `#[ExpectedBundleGap('ID')]`; resolved tests keep their assertions and historical target references; they use normal assertions and are never skipped. This report validates the markers against the inventory.

Categories: `MUST_CONFORMANCE` and `SHOULD_CONFORMANCE` refer to normative JSON:API requirements; `DESIRED_CAPABILITY` is an intentional application contract; `OPTIONAL_FEATURE` is never a conformance failure merely because absent. `APPLICATION_POLICY` belongs to the application; `INFRASTRUCTURE_LIMIT` belongs to the runtime/database/distributed system; `DOCUMENTATION_GAP` describes documentation/contract drift or discoverability. `DX_GAP` describes public integration ergonomics/tooling. `CONFIG_IMPLEMENTATION_GAP` identifies accepted configuration with no corresponding runtime implementation.

Tested bundle `96a1530f3155ddf001b7d1e48fd33e375c382d85`, PHP 8.2.34, PHPUnit 11.5.57.

Current observed failures are also summarized in [current-gaps.json](current-gaps.json). Closed investigations and reviewed IDs are in [history](history/README.md).
