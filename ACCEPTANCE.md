# Acceptance contract and baseline

The application exercises real Symfony HTTP requests through `KernelBrowser`.
Only deterministic fixture setup uses Doctrine directly. Assertions inspect
public documents, headers, subsequent HTTP reads, and persisted behavior; they
do not inspect bundle services, controllers, query SQL, or private metadata.

The bundle is installed and pinned through Composer. Its tested revision and current
counts are recorded by `tools/acceptance-report.php` in
`docs/acceptance-results.json`, `docs/acceptance-matrix.md` and
`docs/acceptance-status.md`. No vendor implementation is patched. Historical
protocol baselines remain available in Git; the inventory retains every existing
gap ID and assertion alongside new application-layer requirements.

## Production application contracts

`tests/Acceptance/Production` uses the `publishing` kernel: real authentication,
resource policies, automatic query scope, safe serializer input groups, publish
business action, application events/recorder, aggregate read model and two-client
concurrency. Only fixture setup and persisted outcome checks use Doctrine directly.
Two independent HTTP processes exercise overlapping writes. Minimal test-only DB
triggers make rollback and overlap observable; production services contain no fault
injection. Large-scale faults/topology remain in Torture.

The old `test` environment deliberately retains its broad write-type fixture
mapping and disables publishing policy. This preserves generic enum/datetime/number
assertions while production HTTP tests enforce the narrower application contract.
The configured YAML groups appear in Symfony metadata but are ignored by the bundle;
five unchanged historical assertions expose `WRITE-MODEL-SERIALIZER-METADATA`.
Never copy `config/acceptance-serializer` into a production serializer mapping.
See [the walkthrough](docs/production-example.md) and
[iteration report](docs/production-iteration.md).

New contracts are classified as `MUST_CONFORMANCE`, `DESIRED_CAPABILITY`,
`APPLICATION_POLICY`, `INFRASTRUCTURE_LIMIT`, or `DOCUMENTATION_GAP`. Existing
`SHOULD_CONFORMANCE` and `OPTIONAL_FEATURE` categories retain their meanings.
The new executable failures are desired generic integration capabilities, not
claims that JSON:API mandates authentication, custom commands or an outbox.
Every new failure has a stable ID, `ExpectedBundleGap` marker, inventory entry and
normal assertion. No skips, no assertions endorsing leaked data, no vendor patch.

## Specification and application decisions

Normative references: [JSON:API 1.1](https://jsonapi.org/format/1.1/) and
[Atomic Operations](https://jsonapi.org/ext/atomic/).

- IDs are string identifiers. `id` is not an attribute or relationship. The
  application accepts `fields[articles]=id` as an identifier-only selector;
  accepting that selector is an application choice, and it must not expose an
  `attributes.id`. Empty fieldsets omit all fields while retaining type/id.
- Requested includes with no related resources require an empty `included`
  array. Included resources must be unique and reachable through linkage.
  Sparse fieldsets can intentionally omit relationship linkage.
- Invalid `Accept` candidates must be ignored if another candidate is usable.
  HTTP `q` values are negotiation metadata. Unknown profiles are ignored.
- Unavailable pagination links may be omitted or null. The configured page
  contract is default size 5, maximum 20. Links preserve the query shape.
- Read-only routes permit GET/HEAD/OPTIONS; writes are 405 because the same URI
  exists with other methods. Atomic attempts are expected to enforce policy
  with 403. Relationship type conflicts use an intentionally consistent 409
  ordinary-write contract; malformed Atomic identifiers use 400.
- Read-only field writes produce 422 validation errors. Required-field and
  relationship validation is 422; known unique conflicts are 409.
- Filter operators inspected in the implementation are `eq`, `neq`/documented
  `ne`, `gt`, `gte`, `lt`, `lte`, `in`, `nin`, `like`, `ilike`, `between`, and
  `isnull`. The executable cases now verify agreement across the public query boundary.
  Boolean groups use `and`/`or`. Empty set semantics, bounded nesting, and
  implicit stable sorting are desired capabilities, not base-spec mandates.
- Atomic results follow the configured `return_policy: always` (200 with a
  result per operation); empty entries must be objects. Per-operation snapshots
  are a desired capability. Asynchronous processing and optional error links
  are outside this contract.
- The exact title-only Atomic article payload is transport-valid but lacks this
  domain's required slug and author. Its acceptance expectation is 422 domain
  validation. A separate fully domain-valid payload proves successful canonical
  creation without ref/href; neither test adds targets to appease the parser.

## UUID identifier contract

`Subscription` stores UUID v4 identifiers as strings. `Newsletter` stores UUID v7
identifiers in a native PostgreSQL `uuid` column using Symfony's Doctrine
`UuidType`, with a typed `Uuid` PHP property and setter. HTTP identifiers remain
RFC 4122 strings in both cases. UUIDs are initialized in constructors rather than
assigned by a post-insert database generator.

`Doctrine/UuidIdentifierTest` proves generated-ID CRUD, Location/self links,
missing resources, route/body ID mismatch, UUID linkage and included resources,
relationship replacement, Atomic updates/removes, and Atomic creation/local-ID
resolution. Client-assigned string UUIDs also work and reject collisions.

`UUID-001` (conversion of client strings before `setId(Uuid)`) and `UUID-002`
(controlled errors for malformed UUID URLs) are resolved on the locked branch.
The local-ID creation workflow supplies a valid resource object including its
public type; stricter resource validation exposed that omission in the old fixture.
These tests do not cover database-generated UUIDs or
binary UUID storage in MySQL.

## Atomic negotiation isolation

The application retains its original public media channel configuration
for `/api/operations` with `request.allowed: ['*']`, so AtomicController's own
negotiator can be exercised through the real HTTP kernel. This does not replace
or decorate any bundle service. Atomic tests still assert rejection of incorrect
media types and unsupported parameters.

A test-only `/api/strict-operations` route uses the identical controller outside
that channel and verifies enabled-extension negotiation (`ATOMIC-012`, now
resolved). Both channel-specific and strict-global expectations remain executable.

## Scope and evidence

The [matrix](docs/acceptance-matrix.md) lists each executed dataset. The
[gap inventory](docs/acceptance-status.md) assigns IDs, priorities, categories,
expected/current behavior, tests, and bundle changes. JSON counterparts are
available beside both documents. HTTP traces show which requests were actually
reached; a failing assertion stops that scenario, so later expectations are not
claimed as verified.

Generated integer ID Atomic creation and the complete local-ID workflow now pass;
a natural UUID lid workflow independently verifies supported lid resolution.
Rollback scenarios use already persisted entities or natural IDs so they really
reach a second operation's validation, missing-resource, relationship, or unique
failure rather than stopping on the generated-ID gap.

Read projection uses the actual bundle DTO API (`dataClass`, `viewClass`,
`ReadProjection::DTO`, `fieldMap`) and its default read mapper. The inspected
Doctrine write path does not integrate the separately declared write mapper
contracts, so no parallel DTO write abstraction is invented. Relationship
aliases use the public accessor/property-path mechanism. Profiles exercise
negotiated/default counts and soft-delete/audit persistence behavior.

Tests rebuild both disposable schemas per case. They need no shared IDs, test
order, arbitrary sleeps, or previous fixture run. Run suites serially against a
shared database pair. No static-analysis/style tooling is configured here;
Composer validation, PHP syntax checks, Symfony boot/container checks, and both
PHPUnit suites provide the project checks.

## Recommended bundle iteration

Use the generated open-gap inventory rather than the historical roadmap.
The remaining normal acceptance concerns are required-precondition error status
consistency and excessive filter depth. Production architecture work is reported
separately in `docs/torture-results.md`: prioritize transaction boundaries and
concurrent write safety before query amplification and pagination optimization.

Take one ID at a time, make its existing assertions pass, then move it out of the
`bundle-gap` group and update the inventory. Do not weaken the executable contract
to match the current implementation.
