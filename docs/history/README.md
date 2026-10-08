# Historical reports

Earlier investigations, old failing baselines and closed gap metadata live here. They explain how the fixed external contract was developed; they do not describe current runtime support or current failures.

Use [release gate](../release-gate.md), [current gaps](../current-gaps.json), [feature coverage](../feature-coverage.md) and [compatibility matrix](../compatibility-matrix.md) for current state. Start application integration with the [dev guide](../dev-guide.md).

`bundle-gaps.json` and `torture-gaps.json` retain stable historical IDs and target tests. Report tools still read them to recognize regressions without weakening assertions. `gap-history.json` records verification snapshots, not the exact commits that fixed each issue. The PARTIAL review and publishing/feature iterations are historical reports; their earlier gap descriptions must not be copied into onboarding.
