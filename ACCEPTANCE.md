# Acceptance contract and baseline

The application exercises real Symfony HTTP requests through `KernelBrowser`.
Only deterministic fixture setup uses Doctrine directly. Assertions inspect
public documents, headers, subsequent HTTP reads, and persisted behavior; they
do not inspect bundle services, controllers, query SQL, or private metadata.

The source baseline is `jsonapi-symfony` main at
`5458778adc87386ffff9c07f009c25906e0971db`, inspected in the local bundle checkout
and installed through Composer. The example's starting main is `98eed869`.
No bundle source or vendor implementation was patched.

Verified baseline: **238 stable tests pass; 78 gap cases fail across 44 inventory
entries; zero unexpected failures, warnings, risky tests, or skips**. The full
suite runs 316 cases on PHP 8.4.26 / PHPUnit 11.5.56 with PostgreSQL 16 and MySQL 8.
The included development Dockerfile targets PHP 8.3; verification reused the
available PHP 8.4 image through `ACCEPTANCE_PHP_IMAGE`.


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
  `isnull`. The parser, whitelist, and compiler do not yet agree on all names.
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

Two desired capabilities remain red: `UUID-001` (ordinary and Atomic creation
must convert client strings before calling `setId(Uuid)`) and `UUID-002`
(malformed UUID URLs must return a JSON:API 400 rather than a Doctrine conversion
500). Constructor-generated UUID object IDs pass; client-assigned UUID object
IDs currently do not. These tests do not cover database-generated UUIDs or
binary UUID storage in MySQL.

## Atomic negotiation isolation

The global strict negotiator currently rejects every `ext`, including enabled
Atomic Operations. The application uses the public media channel configuration
for `/api/operations` with `request.allowed: ['*']`, so AtomicController's own
negotiator can be exercised through the real HTTP kernel. This does not replace
or decorate any bundle service. Atomic tests still assert rejection of incorrect
media types and unsupported parameters; those current inconsistencies are gaps.

A test-only `/api/strict-operations` route uses the identical controller outside
that channel and reproduces the global rejection as `ATOMIC-012`. Removing the
channel prematurely would mask generated-ID, local-ID, transactionality, result,
and validation behavior behind the same 415. Once negotiation is fixed, remove
the isolation and keep both sets of expectations.

## Scope and evidence

The [matrix](docs/acceptance-matrix.md) lists each executed dataset. The
[gap inventory](docs/acceptance-status.md) assigns IDs, priorities, categories,
expected/current behavior, tests, and bundle changes. JSON counterparts are
available beside both documents. HTTP traces show which requests were actually
reached; a failing assertion stops that scenario, so later expectations are not
claimed as verified.

Generated integer ID Atomic creation currently fails before later lid operations
can execute. The complete generated-ID lid workflow remains a runnable gap;
a natural UUID lid workflow independently verifies supported lid resolution.
Rollback scenarios use already persisted entities or natural IDs so they really
reach a second operation's validation, missing-resource, relationship, or unique
failure rather than stopping on the generated-ID gap.

Read projection uses the actual bundle DTO API (`dataClass`, `viewClass`,
`ReadProjection::DTO`, `fieldMap`) and its default read mapper. The inspected
Doctrine write path does not integrate the separately declared write mapper
contracts, so no parallel DTO write abstraction is invented. Relationship
aliases use the public accessor/property-path mechanism. Profiles exercise
negotiated/default counts and intended soft-delete/audit behavior; the latter
hooks are currently scaffolding rather than fully integrated persistence logic.

Tests rebuild both disposable schemas per case. They need no shared IDs, test
order, arbitrary sleeps, or previous fixture run. Run suites serially against a
shared database pair. No static-analysis/style tooling is configured here;
Composer validation, PHP syntax checks, Symfony boot/container checks, and both
PHPUnit suites provide the project checks.

## Recommended bundle iteration

1. **P0: write safety and HTTP reachability.** Check preconditions before writes;
   register OPTIONS; recognize enabled Atomic extensions and unify media parsing.
2. **Atomic contract.** Infer add/update targets; reject id+lid; flush generated
   IDs before resolution; serialize empty result objects; enforce write policies;
   preserve operation ordering/snapshots and public error pointers; resolve hrefs.
3. **Doctrine and query boundary.** Map transaction flush exceptions; align
   operator names and AST support; support DBAL 4 `ilike`; translate attribute
   aliases; validate operand shape/type/depth; provide deterministic sorting.
4. **Documents and profiles.** Complete error titles and linkage pointers;
   handle empty includes/objects; align HEAD validators and Last-Modified;
   integrate per-type, soft-delete, and audit hooks.

Take one ID at a time, make its existing assertions pass, then move it out of the
`bundle-gap` group and update the inventory. Do not weaken the executable contract
to match the current implementation.
