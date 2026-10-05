# Production torture application

This layer complements normal JSON:API acceptance tests with difficult domain,
architecture, concurrency and scale cases. All assertions use HTTP through the
Symfony kernel. The bundle source is not modified.

## Setup and isolation

Start services and install the locked dependencies using README commands.
Create disposable PostgreSQL databases once:

```bash
docker compose exec php php bin/console doctrine:database:create --connection=pgsql --env=torture --if-not-exists
docker compose exec php php bin/console doctrine:database:create --connection=shard_b --env=torture --if-not-exists
docker compose exec php php bin/console doctrine:database:create --connection=replica --env=torture --if-not-exists
docker compose exec php php bin/console doctrine:database:create --connection=mysql --env=torture --if-not-exists
```

Fresh MySQL volumes initialize/grant the torture database automatically. Existing
volumes need `docker compose exec -T mysql mysql -uroot -proot < docker/mysql/init.sql`.
Fixtures drop/recreate schemas and refuse databases whose names lack `_torture`.
Never configure these environments against production. Tests reset their own
data and can run independently. Do not run two torture PHPUnit processes against
the same databases simultaneously.

```bash
docker compose exec php composer test:extreme
docker compose exec php composer test:performance
docker compose exec php composer test:chaos
docker compose exec php composer test:torture:stable
docker compose exec php composer test:torture:gaps
```

Normal `composer test:stable` never discovers torture tests or the large fixture.
Performance fixtures include 100k tasks, 10k users, 5k tags and 1M label links.
`TORTURE_ROWS` changes the offset benchmark size; dedicated high-cardinality
tests use 20k tasks with 10k on one project.

## Responsibility boundary

The application owns domain mappings, tenant selection, database snapshots,
fault injection and measurement. It routes through Doctrine's public
`ManagerRegistry` and the bundle's repository contract. Tenant selection is
request-scoped; identical identifiers remain local to the selected manager.
An explicit foreign-tenant marker is rejected by application policy. Separate
tests without that policy exercise bundle relationship lookup and actual
cross-manager/cross-shard Atomic requests.

No application transaction wrapper, eager-loading substitute, query complexity
guard or retry hides bundle behavior. Without distributed transactions, rejecting
a multi-boundary Atomic batch before mutation is the desired safe contract.
Tests deliberately remain red when the bundle cannot provide it. A same-connection
batch must commit or roll back all participating operations; unrelated connections
must not be enlisted. An injected MySQL commit failure therefore must not fail a
PostgreSQL-only batch. No distributed transaction is expected.

The replica is a separately seeded deterministic snapshot, not live replication.
Instrumentation records the physical database actually used by the driver.
Within-write freshness is a bundle integration requirement; lag on independent
GETs is an infrastructure limit. Doctrine's primary pinning and ORM identity map
in an unreset worker are host lifecycle behavior, demonstrated explicitly.
Worker tests distinguish that policy from profile, include, criteria, local-ID,
negotiation and conditional-response state that must not leak.

Fault tests inject SQLSTATE failures at the second UPDATE, a failure committing
MySQL, and a real PostgreSQL row lock with a 100ms lock timeout. Concurrency uses
independent PHP processes/connections released by an IPC barrier, without sleeps.
The barrier establishes simultaneous starts, not a deterministic SQL interleaving;
repeat chaos runs to increase race exposure.

The opt-in `X-Torture-Policy: ingress` enforces 1 MiB / 2000-linkage application
limits. Separate requests bypass that policy to exercise bundle handling of
multi-megabyte text, malformed UTF-8, deep JSON, huge IDs and unknown members.

## Metrics and reports

Clear old traces before a complete run, then generate reports even if known gaps
make PHPUnit return nonzero:

```bash
docker compose exec php php -r 'foreach (["metrics", "scenarios", "large-benchmark", "replica"] as $n) { file_put_contents("var/torture/".$n.".ndjson", ""); }'
docker compose exec php composer test:torture:full
docker compose exec php composer torture:report
```

Reports validate complete JUnit against discovered tests and use the installed
Composer revision. `docs/torture-results.md` is the scenario/gap matrix;
`docs/performance-results.json` records individual status, query count, unique SQL,
fetched rows, timing, peak/baseline memory, response bytes, dataset and transaction
events. Raw normalized SQL lives in ignored `var/torture/metrics.ndjson`; bind
values and credentials are not recorded. DBAL fetched rows are not an exact ORM
hydration count. PHP memory includes the already-loaded process baseline.

Structural budgets compare pages of 5 and 20 and allow ten extra queries for
bounded metadata/setup overhead, with absolute limits of 12–32 queries depending
on include shape. Wall-clock timings are evidence, not CI assertions.
Always-linkage enumerates large associations even without includes; the lean
environment demonstrates `when_included`. Sparse fields provide a second control.
Offset timings at pages 1/10/100/1000 inform future keyset work without requiring it.

`#[Group('torture-gap')]` runs normal failing assertions, never skips. The
`ExpectedTortureGap` marker links reviewed architectural categories to tests.
Passing historical markers are reported as resolved; only observed failures are
open. Unexpected failures remain distinct and make the reporter fail.

## Historical findings on f02849d

This section records the old revision, not the current gap list. Current results
and open/resolved markers are generated in [torture-results.md](docs/torture-results.md).

62 torture cases: 40 PASS, 17 BUNDLE_GAP, 4 APPLICATION_POLICY and 1
INFRASTRUCTURE_LIMIT. No unexpected failures or skipped tests. The unique-create
race now produces a controlled conflict. A depth-limit case is green with the
torture complexity budget; the normal acceptance configuration still exposes its
own excessive-depth gap. Green cases do not imply every architecture is supported.

- **P0:** PostgreSQL/MySQL and cross-shard Atomic batches mutate instead of
  rejecting independent transaction boundaries. Failing a second-manager commit
  after a PostgreSQL write leaves a partial commit. Two overlapping If-Match
  writers can both succeed; prechecking alone does not provide atomic concurrency.
- **P1:** Default task collection SQL grows from 17 queries at five roots to 62
  at twenty. To-one includes grow 19→64, to-many 22→52, nested 21→66. Sparse
  collections use two queries. Batch loading/linkage is the next structural fix.
- **P1:** To-many joins produce ten roots where twenty are requested. Include
  overflow returns a controlled 400, but only after 13,316 fetched rows for a
  configured cap of 250. Filter node/operand complexity and unsupported composite
  identifiers need earlier diagnostics/limits.
- **P2:** On 100k tasks / 1M label links, sparse pages 1/10/100/1000 use two SQL
  queries each, taking approximately 50/65/65/94 ms in this run. This is evidence
  for future offset/keyset comparison, not a machine-independent latency promise.

Replica GETs reach the snapshot; POST/PATCH/DELETE and relationship/Atomic write
responses use fresh primary state. Independent GETs may remain stale. Unreset
long-running Doctrine connections stay primary-pinned by host policy. Actual
replica failure and lock timeout return controlled JSON:API errors. SQLSTATE
failures roll back a single-boundary Atomic batch. Application tenant isolation,
worker profile/include/media/local-ID state and compound-cache changes pass.

Next bundle iteration should address the P0 transaction/concurrency guarantees
first, then root pagination and bounded graph/query execution. Replication,
sharding infrastructure and distributed transaction implementation remain outside
the bundle's responsibility; safe integration and rejection are inside it.
