# Published RC: GitHub execution evidence

All four runs installed the actual `v1.0.0-RC` package from committed locks and completed the common fresh-install gate, boot/documentation checks, tooling checks, Acceptance (including Production/Features) and Torture.

| Fixture | Actual PHP / Symfony | Acceptance | Production / Features | Torture | GitHub run | Retained artifact |
|---|---|---|---|---|---|---|
| main | 8.2.34 / v7.4.20 | 683 | 82 / 280 | 62 | [success](https://github.com/AlexFigures/example-jsonapi-bundle/actions/runs/37767998934) | [ZIP](release-evidence/v1.0.0-rc/main.zip) |
| sf74 | 8.2.34 / v7.4.20 | 683 | 82 / 280 | 62 | [success](https://github.com/AlexFigures/example-jsonapi-bundle/actions/runs/37767998693) | [ZIP](release-evidence/v1.0.0-rc/sf74.zip) |
| sf81 | 8.4.26 / v8.1.8 | 683 | 82 / 280 | 62 | [success](https://github.com/AlexFigures/example-jsonapi-bundle/actions/runs/37767998616) | [ZIP](release-evidence/v1.0.0-rc/sf81.zip) |
| sf82 | 8.4.26 / 8.2.x-dev | 683 | 82 / 280 | 62 | [success](https://github.com/AlexFigures/example-jsonapi-bundle/actions/runs/37767998002) | [ZIP](release-evidence/v1.0.0-rc/sf82.zip) |

The ZIP archives are the downloaded GitHub artifacts, retained unchanged in Git. Their SHA-256 values and run/source revisions are in [github-release-evidence.json](github-release-evidence.json). JUnit hashes were checked against each artifact’s primary compatibility evidence. Nested cross-platform JSON files in an artifact are previous reports; use its primary `docs/compatibility-evidence.json` for that run.

The stable sf74 and sf81 runs have clean source snapshots. Symfony 8.2-dev is forward evidence only; its recorded dirty-snapshot flag is retained explicitly. Its Composer manifest/lock and executable contract hashes match the committed fixture, but it does not certify stable Symfony 8.2 support or receive an immutable stable-support tag.

Main is the canonical PHP 8.2 / Symfony 7.4 consumer. [Compatibility matrix](compatibility-matrix.md), [release workflow](compatibility-workflow.md).
