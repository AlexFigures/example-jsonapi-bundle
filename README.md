# JSON:API Symfony production example and acceptance application

This is both a usage example and a black-box acceptance application for
[jsonapi-symfony](https://github.com/AlexFigures/jsonapi-symfony). Tests send HTTP
requests through Symfony's routing/kernel, the installed bundle, Doctrine,
Serializer, and Validator. They never instantiate bundle controllers or services.

The bundle is an external Composer dependency. The repository serves three roles:
production onboarding/reference application, independent black-box acceptance
application, and production torture/stress laboratory. Desired capabilities use
normal runnable assertions, including intentionally failing `bundle-gap` cases.
No vendor code is patched and no generic JSON:API workaround is installed.

## Using this repository

### A. I want to learn how to use the bundle

Read [the production walkthrough](docs/production-example.md), then this short sequence:

1. [Article resource and serializer groups](src/PgEntity/Article.php).
2. [JSON:API configuration](config/packages/jsonapi.yaml) and [service integration](config/services.yaml).
3. [Publish handler](src/Application/Article/PublishArticle.php) and `Article::publish()`.
4. [Authentication context](src/Security/PublishingContext.php) and [authorization policy](src/Security/ArticlePolicy.php).
5. [Scoped queries](src/JsonApi/ScopedArticleRepository.php) and [custom search](src/JsonApi/Filter/ArticleSearchFilter.php).
6. [The HTTP editor journey](tests/Acceptance/Production/PublishArticleTest.php).

Normal environments require `Authorization: Bearer reader`, `editor-a`, `editor-b`
or `admin` on `/api/` requests. These are deterministic demo credentials to replace
with a real identity provider. Run `composer test:production` for working publishing
contracts and `composer test:production:gaps` for missing capabilities. The
walkthrough identifies the open graph-scope boundary and aggregate endpoint gap.

### B. I want to verify bundle behavior

Use [tests/Acceptance](tests/Acceptance), [ACCEPTANCE.md](ACCEPTANCE.md),
[acceptance-status.md](docs/acceptance-status.md), and the generated
[matrix](docs/acceptance-matrix.md). `test` preserves the original protocol fixture
surface; `publishing` exercises authentication and safe inputs against disposable
test databases. Full-suite failures remain visible and classified.

### C. I want to inspect production edge cases

Use [tests/Torture](tests/Torture), [TORTURE.md](TORTURE.md), and
[performance results](docs/performance-results.json). Shards, replicas, large graphs,
worker leakage and cross-manager faults stay in this separate laboratory.

The [iteration report](docs/production-iteration.md) records scenarios, executable
gaps, green contracts and DX findings. For the tested dependency revision and current
counts, use the generated acceptance results rather than a hardcoded README baseline.

## Domain and layout

| Resource | Storage | Purpose |
|---|---|---|
| `authors` | PostgreSQL | Generated IDs, unique email, inverse articles, validation |
| `articles` | PostgreSQL | Enum, JSON, datetime, boolean/numeric fields, required author, nullable editor, tags |
| `tags` | PostgreSQL | Many-to-many linkage, includes, default relationship-count profile |
| `categories` | PostgreSQL | Parent/children hierarchy and archived records for soft-delete profile expectations |
| `comments` | MySQL | Independent entity manager; scalar `articleId` crosses storage boundaries |
| `audit-logs` | PostgreSQL | Read-only INDEX/SHOW operations |
| `subscriptions` | PostgreSQL | Natural UUID string ID; client IDs explicitly allowed |
| `newsletters` | PostgreSQL | Native `uuid` column with Symfony `Uuid` object; UUID recipient and previous-edition relationships |
| `article-summaries` | PostgreSQL projection | Bundle DTO projection of Article, exposing only `headline` |
| `author-publishing-statistics` | Application aggregate | Custom-route-only read model; currently exposes a link-generation gap |
| Publication notifications | PostgreSQL, not an API resource | Transactional local side effect of publication |

Entity mappings live in `src/PgEntity` and `src/MysqlEntity`; DTO metadata lives in
`src/Api`. `config/packages/jsonapi.yaml` explicitly enables writes, cache,
profiles, query limits, and Atomic Operations. Detailed tests live in
`tests/Acceptance`, with shared HTTP assertions in `Support`. One broad publishing
journey remains under `Smoke`.

`Doctrine/UuidIdentifierTest` exercises both UUID string IDs and native Doctrine
UUID objects through HTTP CRUD, linkage, includes, and Atomic Operations. Native
UUIDs are generated in the entity constructor. Client-assigned IDs on typed
`Uuid` properties and malformed UUID URLs now pass (`UUID-001`/`UUID-002` resolved)
and run in the stable suite.

## Production torture layer

The normal acceptance suite remains small and deterministic. Separate `extreme`,
`performance`, and `chaos` suites exercise production topology and failure
boundaries without making the bundle responsible for sharding, replication,
tenancy, or distributed transactions:

```bash
composer test:extreme
composer test:performance
composer test:chaos
php bin/console app:torture:seed small --env=torture
php bin/console app:torture:seed medium --env=torture
php bin/console app:torture:seed large --env=torture
composer torture:report
```

`small` seeds 100 tasks, `medium` 10,000, and `large` 100,000 with association
rows and dense task labels. The separate performance suite runs the 100k
benchmark by default; `TORTURE_ROWS` changes its size (20k–1M).
benchmark. The isolated torture environment uses databases marked `_torture`
and refuses to reset other databases. It records status, physical database,
SQL count and shape, fetched rows, wall time, peak memory, response size, and
transaction events in `var/torture/`; summaries are written to
`docs/torture-results.md` and `docs/performance-results.json`.

The application supplies tenant context and opt-in ingress limits. Cross-manager
Atomic rejection is an executable expectation against the bundle itself;
no application guard implements it or hides partial persistence.
All resource behavior remains exercised through the bundle's HTTP routes and
public data-layer integration. Red tests are tagged `torture-gap`; application
policy and infrastructure observations are documented separately. See
[TORTURE.md](TORTURE.md) for database setup, responsibility boundaries and reporting.

The bundle dependency is pinned in `composer.lock` to the tested
`fix/acceptance-gaps` branch. Reports obtain its installed commit automatically.

`published-at` maps to PHP `publishedAt`. The `reviewedBy` relationship maps to
Doctrine's `reviewer` property through the bundle's public `propertyPath` API.
Articles serve as the cache/precondition resource. The bundle's `VersionResolverInterface`
selects representations by profile; it is not an entity modification-version resolver.

## Start and install

Requirements: Docker Compose v2. The included image has PHP 8.3, Composer, and
both PDO database drivers. No host PHP installation is required.

```bash
docker compose build php
docker compose up -d
docker compose exec php composer install
```

The app is served at `http://localhost:8080`. Database ports are internal to
Compose, avoiding interference with other local projects. Override occupied
ports/subnets when necessary:

```bash
APP_PORT=18080 ACCEPTANCE_SUBNET=172.30.248.0/24 docker compose up -d
```

`DATABASE_URL` and `MYSQL_URL` are set to the corresponding Compose service names.
Use `.env.local` / `.env.test.local` for another environment. Test schemas use the
`_test` suffix; MySQL's initialization script grants the test database to the app
user, while PostgreSQL's container user can create its test database.

## Prepare databases and representative data

```bash
docker compose exec php php bin/console doctrine:database:create --connection=pgsql --if-not-exists
docker compose exec php php bin/console doctrine:database:create --connection=mysql --if-not-exists
docker compose exec php php bin/console doctrine:schema:update --force --em=pgsql
docker compose exec php php bin/console doctrine:schema:update --force --em=mysql

docker compose exec php php bin/console doctrine:database:create --connection=pgsql --env=test --if-not-exists
docker compose exec php php bin/console doctrine:database:create --connection=mysql --env=test --if-not-exists
```

For populated demo data, run `docker compose exec php php bin/console app:acceptance:seed`.
This command **drops/recreates both configured schemas** and prints generated IDs.
Use disposable databases. Tests invoke the same fixture builder before each case,
so every test is independently runnable and IDs are resolved from named fixture
objects. Do not run suites concurrently against the same test databases.

## Run the executable contract

```bash
# Supported behavior: must be green.
docker compose exec php composer test:stable

# Full acceptance suite: currently intentionally red.
docker compose exec php composer test:full

# Only known bundle gaps: assertions still execute normally.
docker compose exec php composer test:gaps

# Focused class, method, or gap ID.
docker compose exec php php bin/phpunit tests/Acceptance/Query/IncludeTest.php
docker compose exec php php bin/phpunit --filter testPartialUpdatePreservesOmittedFields
```

A gap ID is an attribute/inventory marker, not a PHPUnit filter name; find its
method in [the gap inventory](docs/acceptance-status.md), then use `--filter`.
PHPUnit warnings and risky tests fail the run. There is no percentage coverage
threshold or configured PHPStan/CS Fixer in this application.

To regenerate verified reports, record the full run, then run the reporter even
though PHPUnit returns nonzero for known gaps:

```bash
docker compose exec php php -r 'file_put_contents("var/acceptance-http.ndjson", "");'
docker compose exec -e ACCEPTANCE_RECORD_HTTP=1 php php bin/phpunit --log-junit var/acceptance-junit.xml
docker compose exec php composer acceptance:report
```

The reporter validates gap IDs/groups and writes:

- [Behavioral coverage matrix](docs/acceptance-matrix.md): scenarios, expected HTTP statuses, observed statuses, results, gap IDs.
- [Readable gap inventory](docs/acceptance-status.md), backed by [reviewed JSON metadata](docs/bundle-gaps.json).
- [Machine-readable results](docs/acceptance-results.json), including failure details.

It exits nonzero for unexpected failures or skipped cases. A passing tagged gap
is a candidate for review and promotion into the stable suite; reports never
reclassify failures or change assertions automatically.

## HTTP examples and routes

```bash
docker compose exec php php bin/console debug:router
docker compose exec php php bin/console debug:router --env=test

curl -g 'http://localhost:8080/api/articles?include=author,tags&fields[articles]=title,author,tags&sort=title,id&page[size]=2' \
  -H 'Accept: application/vnd.api+json'

curl 'http://localhost:8080/api/authors' \
  -H 'Content-Type: application/vnd.api+json' \
  -H 'Accept: application/vnd.api+json' \
  -d '{"data":{"type":"authors","attributes":{"name":"New Author"}}}'

curl 'http://localhost:8080/api/operations' \
  -H 'Content-Type: application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"' \
  -H 'Accept: application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"' \
  -d '{"atomic:operations":[{"op":"add","data":{"type":"authors","attributes":{"name":"Atomic Author"}}}]}'
```

The canonical Atomic example now passes on the locked branch. Resource
updates/removes via explicit `ref` and relationship writes
are also exercised. Swagger UI is at `/_jsonapi/docs`.

## Contract boundaries

See [ACCEPTANCE.md](ACCEPTANCE.md) for specification decisions, the Atomic media
channel isolation, observed outcomes, and recommended bundle patches. Optional
features such as asynchronous 202 responses and optional error links are not
reported as conformance failures. The bundle is left unchanged in this iteration.
