# Version-pinned external consumers

`main` is the canonical Symfony **7.4 / PHP 8.2** reference application during pre-RC stabilization. It replaces the unsupported formal 7.3 fixture. Move main to 8.1/8.4 only after the published bundle permits that platform and the full external gate passes. Main always has one explicit platform; it is not a multi-version Composer mix.

The target definitions in [targets.json](../compatibility/targets.json) describe independent compatibility branches. All branches share application code and executable assertions. Platform-only changes are composer.json/lock, compatibility/platform.json, Docker runtime and documented normal framework migrations. Do not cherry-pick report PASS values from another branch.

| Branch | Runtime | Dependency series | Role |
|---|---|---|---|
| compat/symfony-7.4-php-8.2 | PHP 8.2 | Symfony 7.4.*, DoctrineBundle 2.x, ORM 3.x, DBAL 4.x | LTS required fixture |
| compat/symfony-8.1-php-8.4 | PHP 8.4 | Symfony 8.1.*, DoctrineBundle 3.x, ORM 3.x, DBAL 4.x | Required fixture, prepared until package resolution succeeds |
| compat/symfony-8.2-php-8.4 | PHP 8.4 | Symfony 8.2.*@dev initially | Non-blocking forward fixture; no stable support claim |

## Prepare a branch

Start each branch from the same committed application contract, then change only its platform manifest:

```bash
git switch -c compat/symfony-7.4-php-8.2
python3 tools/compatibility.py --prepare sf74 --mode stabilization --bundle 'dev-main#EXACT_40_CHARACTER_SHA'
COMPAT_PHP_VERSION=8.2 docker compose up -d --build
docker compose exec -T php composer update --with-all-dependencies --no-interaction --no-scripts
```

For sf81/sf82 substitute the target/branch and use PHP 8.4. Resolve Composer on the real runtime, commit the generated lock, then run the gate. Never use --ignore-platform-reqs, a path repository, a fabricated package version or modified vendor metadata. If the bundle requires only ^7 Symfony components, preserve the Composer solver error as a packaging compatibility finding; an unresolved manifest is not a valid locked fixture.

The helper requires an explicit stabilization SHA. It stores a normal branch constraint in composer.json, the expected commit in compatibility/platform.json, and the resolved source in composer.lock. This passes strict Composer validation, which warns against commit-ref requirements. Composer still reads published branch dependency metadata; the evidence verifies the installed commit against the expected one.

## Common gate

```bash
python3 tools/release-gate.py --run --clean-install
```

Use `--update` to update the bundle within the explicit manifest constraint. To test a different commit or published version, prepare that constraint first, resolve and commit the lock. Release proof uses the actual Composer package, not a local checkout.

The clean install uses unique empty Docker volumes for vendor and Symfony cache, leaving existing directories intact. It executes Composer install, check-platform-reqs, validate --strict, application boot, full Acceptance (including Production/Features), and full blocking Torture. Each CI branch obtains its PHP runtime from its pinned target; a mismatched compat branch name fails. Tests recreate disposable schemas. PostgreSQL database creation is explicit in CI; MySQL test databases are initialized by docker/mysql/init.sql.

Generated evidence includes actual PHP/FrameworkBundle/Doctrine versions, bundle version+commit, example HEAD and dirty-snapshot flag, normalized executable contract digest, Composer lock/manifest digests, raw JUnit/HTTP hashes, fresh-install identity and all suite results. An all-green dirty snapshot can be diagnostic evidence; immutable release evidence needs committed source/lock/reports.

GO is a **platform execution** result: correct actual runtime and installed package, all suites pass, no skips/regressions/stale markers, coherent source and dependency digests. Known failing assertions also produce NO-GO. This is not automatic approval of the bundle's overall API freeze. The release owner must separately confirm public-surface decisions and every required platform. A Symfony 7.4 GO cannot certify Symfony 8.1.

## RC and final evidence

1. Verify an exact stabilization commit on all required locked branches; optionally run sf82 forward. Report each platform's GO/NO-GO.
2. After the bundle publishes 1.0.0-RC1, prepare each branch with `--mode release --bundle 1.0.0-RC1`, resolve its lock through Composer, commit and perform the fresh-install full gate.
3. Commit resulting reports and use `python3 tools/compatibility.py --tag bundle-1.0.0-rc1-sf74` (and sf81/sf82 only when verified). The helper rejects non-release, failed or dirty evidence and existing tags; it does not push.
4. After publishing 1.0.0, repeat with the actual final package and `bundle-1.0.0-sf74` / `sf81` tags. RC evidence does not substitute for final package installation.

No final/RC tags are created during pre-release preparation. Tags identify an immutable example commit, published bundle version and platform. Compare contract digests and reviewed normal migration diffs when merging proofs across branches. Never merge another branch's Composer lock over the current platform.

## Future Symfony releases

When Symfony 8.2 stable exists, replace sf82's dev constraint with 8.2.*, resolve on PHP 8.4, commit the new lock and run the complete gate against a published compatible bundle 1.x. Only successful stable release-mode proof promotes the platform to SUPPORTED. Failed package resolution or different JSON:API statuses/codes/pointers/representation are compatibility findings; fix the bundle and rerun its patch/minor package. Preserve assertions unless the public contract deliberately changes.

Current facts are generated in [compatibility-matrix.md](compatibility-matrix.md) and [release-compatibility.md](release-compatibility.md). Prepared definitions, old green runs and unsupported solver failures never become PASS through documentation alone.
