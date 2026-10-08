# Compatibility matrix

Only exact executed environments produce PASS. SUPPORTED requires a successful stable-platform release-mode run; development fixtures are forward evidence. Prepared targets are not compatibility claims.

| Target branch | PHP | Symfony | Bundle | Acceptance | Torture | Status |
|---|---|---|---|---|---|---|
| [compat/symfony-7.4-php-8.2](compatibility-evidence/sf74.json) | 8.2.34 | v7.4.20 | dev-main | {'PASS': 680, 'SKIP': 0} | {'PASS': 62} | FORWARD TESTED / DEV |
| compat/symfony-8.1-php-8.4 | 8.4.26 | 8.1.* | pending | not executed | not executed | PREPARED / no support claim |
| compat/symfony-8.2-php-8.4 | 8.4.26 | 8.2.*@dev | pending | not executed | not executed | PREPARED / no support claim |

Canonical main policy and RC/final workflows: [compatibility-workflow.md](compatibility-workflow.md). No 8.2 stable proof is claimed by an 8.2-dev run.
