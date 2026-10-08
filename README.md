# Symfony JSON:API example

A Composer consumer of `alexfigures/symfony-jsonapi-bundle`: a publishing application with PostgreSQL articles/authors and MySQL comments, plus independent HTTP acceptance and production fault tests. Bundle source is installed from its committed lock; no vendor patches.

The canonical consumer uses Symfony 7.4 / PHP 8.2. Exact executed platforms and package revisions are in the [compatibility matrix](docs/compatibility-matrix.md). A `dev-main` run proves its locked commit; release proof requires an actual published RC/final package.

## Quick start

Requirements: Git, Docker Compose v2 and Python 3 for release tooling. No host PHP or frontend build is needed.

```bash
git clone https://github.com/AlexFigures/example-jsonapi-bundle.git
cd example-jsonapi-bundle
bash tools/dev-setup.sh
```

Setup installs the lock, prepares databases and resets disposable demo fixtures. The app is at `http://localhost:8080`; [Swagger](http://localhost:8080/_jsonapi/docs), [OpenAPI JSON](http://localhost:8080/_jsonapi/openapi.json) and [JSON Schema](http://localhost:8080/_jsonapi/schemas) are enabled. Docker's router script passes dotted URLs to Symfony Runtime.

```bash
curl --fail-with-body -g 'http://localhost:8080/api/articles?include=author,tags&sort=title,id&page[size]=2' \
  -H 'Accept: application/vnd.api+json' -H 'Authorization: Bearer reader'
```

If the port/subnet is occupied, export `APP_PORT=18080` and `ACCEPTANCE_SUBNET=172.30.248.0/24` before setup. On Linux, export `LOCAL_UID=$(id -u)` and `LOCAL_GID=$(id -g)` if your account differs from 1000. Local credentials belong in `.env.local` / `.env.test.local`.

## Use the bundle

Follow the [dev guide](docs/dev-guide.md): resource → safe writes → authorization/scope → filters/sorts → publish → events → projections/profiles → Atomic/concurrency. The [cookbook](docs/cookbook.md) links every important extension family to application code and HTTP tests. Demo bearer credentials are `reader`, `editor-a`, `editor-b`, `admin`; replace them with real authentication in your application.

## Verify the bundle

```bash
python3 tools/release-gate.py --run --clean-install
```

The command creates disposable test databases, installs the lock into fresh vendor/cache volumes, checks Composer and real HTTP documentation, runs all Acceptance (including Production/Features) and Torture, and generates provenance and GO/NO-GO. It requires Python 3 and Docker. Suites reset their own schemas; do not run concurrent suites against the same databases.

Read the [release workflow](docs/compatibility-workflow.md), [release gate](docs/release-gate.md), [feature coverage](docs/feature-coverage.md), [compatibility matrix](docs/compatibility-matrix.md), [Acceptance contract](ACCEPTANCE.md), and separate [Torture guide](TORTURE.md). [Current gaps](docs/current-gaps.json) lists current observed failures. [History](docs/history/README.md) contains earlier investigations and resolved gap metadata; it is not current support evidence.

`src/PgEntity` and `src/MysqlEntity` contain database-specific entities; `src/Api` contains representations, `src/Application` business commands, and `src/JsonApi` public adapters. `config/packages` configures the bundle/framework; `tests/Acceptance` specifies HTTP behavior and `tests/Torture` contains the separate topology/fault laboratory.
