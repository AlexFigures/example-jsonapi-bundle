# Publishing application walkthrough

Start with `src/PgEntity/Article.php`, `config/packages/jsonapi.yaml`,
`src/Application/Article/PublishArticle.php`, `src/Security/ArticlePolicy.php`,
`src/JsonApi/ScopedArticleRepository.php`, and
`tests/Acceptance/Production/PublishArticleTest.php`.
These files form the normal application example. Torture infrastructure is not
required to understand or run it.

The application is an independent Composer consumer. No bundle implementation
is patched. Generated CRUD, validation, relationship resolution, query parsing,
pagination, includes, representation building and HTTP negotiation remain bundle
responsibilities. Application adapters delegate those operations to the installed
bundle rather than implementing another JSON:API engine.

## Environments and credentials

The normal `dev`/`prod` environments enable publishing policy. HTTP production
acceptance cases use `publishing`, with the same policy and serializer attributes,
but disposable `_test` PostgreSQL/MySQL databases and a test observer. Every
`/api/` request requires a deterministic `Authorization: Bearer ...` credential:

| Credential | Application identity | Permission |
|---|---|---|
| `reader` | Ada's scope | Read articles; no writes or publication |
| `editor-a` | Ada's scope | Create/edit/delete/publish own articles; manage tags; assign self as editor |
| `editor-b` | Grace's scope | Same rules within Grace's ownership |
| `admin` | All authors | Manage articles and reference resources; reassign authors |

`PublishingAuthentication` performs authentication at the Symfony HTTP boundary.
`PublishingContext` translates the credential into a request-local identity; it
stores no current-user state between requests. These fixed demo credentials are
an onboarding/test adapter. A deployed application supplies its own authenticated
identity provider and credential validation. The ownership model here deliberately
uses the existing Author and its email rather than introducing organizations.

`test` retains the original unprotected protocol acceptance setup. Its explicitly
test-only serializer mapping (`config/acceptance-serializer/Article.yaml`) retains
the historical writable enum/datetime/integer fields so original type round-trip
assertions are preserved. The bundle currently ignores those effective YAML groups
(`WRITE-MODEL-SERIALIZER-METADATA`); five historical write/workflow cases now expose
that integration gap rather than being rewritten to pass. That mapping is not loaded in `publishing`, `dev` or `prod`.
The legacy `preconditions` environment also disables publishing policy to isolate
its existing precondition contract. `publishing_di` is a minimal diagnostic
configuration: its public profile with constructor DI currently cannot boot.

## Entity/resource and safe inputs

`Article` combines Doctrine mappings with `JsonApiResource`, `Attribute`,
`Relationship`, filter/sort metadata and Symfony serializer groups. The bundle
creates CRUD routes for it. `Author` and `Tag` remain the existing content domain.

Clients write title, slug, content, metadata, featured, rating and permitted
relationships. Server-owned `createdAt`, `updatedAt`, `views`, `published-at` and
`status` have only `articles:read` groups. The entity constructor supplies timestamps,
zero views and draft state. A Doctrine `PreUpdate` callback maintains update time.
Publication changes state through a domain method. The bundle's public
`denormalizationContext` enforces the groups, including Atomic writes, with 422
errors and attribute pointers. Even administrators cannot write those fields.
This example needs no input DTO or generic compatibility layer. A richer aggregate
may benefit from a first-class write/input model, but its absence is not declared
a gap merely because this example uses entities and groups successfully.

## Generated routes and authorization

`ArticlePolicy` owns the business decisions. Reading and updating are distinct;
deleting, associating and publishing are explicit operations. A permitted source
resource does not grant permission to associate every existing target. Editors
cannot reassign authors and can assign only themselves as editor. Reference-resource
mutations are administrator-only, including inverse relationship writes: changing
an Author's email or inverse articles must not bypass ownership.

Three Symfony decorators wrap stable public bundle contracts:

| Application adapter | Public contract | Application behavior |
|---|---|---|
| `ScopedArticleRepository` | `Contract\Data\ResourceRepository` | Require visibility for SHOW; append a server scope for INDEX/projections |
| `AuthorizedArticleProcessor` | `Contract\Data\ResourceProcessor` | Check POST/PATCH/DELETE and embedded relationships before delegating |
| `AuthorizedArticleRelationships` | `Contract\Data\RelationshipUpdater` | Check standalone relationship replacements/additions/removals |

`config/services.yaml` decorates interface service IDs, without naming internal
Doctrine controllers, locators or flush services. All parsing, persistence and
transaction work is delegated. Atomic mutation tests verify the same permissions.
The policies are application responsibility; consistent invocation of public
extension seams on generated routes is bundle responsibility.

## Scope, search and query composition

`PublishingRules` adds a bound ownership condition to a cloned `Criteria` using
its public `customConditions` property. It uses an AND condition independent of
client filters. Clients cannot replace it by filtering for Grace while logged in
as editor A. Scope composes with filters, sort, page links, includes of permitted
authors and sparse fieldsets on the root collection. Administrators omit the scope.

`ArticleSearchFilter` implements the public `FilterHandlerInterface`, is registered
with `jsonapi.filter.handler`, and is declared through `FilterableField(customHandler:)`.
Its small application search covers title/content, accepts one 3–100 byte term,
binds SQL parameters and escapes LIKE wildcards. It is substring search, not a
language-aware or indexed search service.

```bash
curl -H 'Accept: application/vnd.api+json' \
  -H 'Authorization: Bearer editor-a' \
  'http://localhost:8080/api/articles?filter[search]=Body&filter[status]=draft&sort=-title&page[size]=2&include=author&fields[articles]=title,author'
```

**Open `QUERY-SCOPE-GRAPH` (P1):** root scope is not a complete visibility boundary.
Related collections, included foreign articles and relationship linkage still
bypass it. The executable tests demand no foreign objects or identifiers. A
single mandatory resource-scope API must cover graph reads and identifier discovery
before pagination. This example deliberately does not post-filter pages, replace
include traversal or disguise that missing integration. Until this gap is resolved,
the demonstrated scope cannot be used as a complete production access boundary.

## Publish command and business validation

`JsonApiCustomRoute(handler:)` on Article declares
`POST /api/articles/{id}/publish`. The installed API treats custom route paths
literally, so the `/api` prefix is explicitly part of this path. The handler
implements public `CustomRouteHandlerInterface`, receives a preloaded Article
through `CustomRouteContext`, and returns `CustomRouteResult::resource`.
The bundle owns media negotiation, unknown-resource responses, serialization and
transaction wrapping. The application uses Doctrine's public `flush()` for its
business change; no internal bundle flusher is used.

The handler authorizes publication, invokes `Article::publish(now)`, translates
`PublicationRejected` through `CustomRouteResult::conflict/ unprocessable`, and
returns the published representation. Validation errors identify content as the
missing publishing condition in the HTTP example.

| State/condition | Deliberate command result |
|---|---|
| Draft with title, content and author | 200; persist published state and timestamp |
| Already published | 200; preserve timestamp; no repeated application side effect |
| Archived | 409; unchanged |
| Blank content | 422; unchanged |
| Unknown or malformed article identifier | 404 |
| Reader or another owner | 403 |

```bash
curl -X POST -H 'Accept: application/vnd.api+json' \
  -H 'Content-Type: application/vnd.api+json' \
  -H 'Authorization: Bearer editor-a' \
  http://localhost:8080/api/articles/1/publish
```

Create a draft with generated POST, PATCH its text, then call publish; see the
complete HTTP journey in `PublishArticleTest::testEditorJourney`. Direct PATCH
of `status` is rejected. Repeated-command success is an **APPLICATION_POLICY**;
generic `Idempotency-Key` replay storage is not claimed or required. The example
asserts sequential repeats; it does not promise a distributed command deduplication
service or exactly-once delivery.

## Events, transactions and observable side effects

`ArticlePublished` is an application event, emitted only on a real draft transition.
`PublicationRecorder` persists one `PublicationNotification` in the same PostgreSQL
transaction. A unique article identifier also protects the local recorder from
duplicate rows. This is a deterministic local side effect, not external delivery
or a full outbox implementation. Business validation emits no publication event.
A test-only PostgreSQL write rejection verifies rollback of both state and recorder.

The application event intentionally fires **before flush and before commit**.
The listener may persist local work in the transaction, but must not send an external
notification assuming publication has already committed. HTTP lifecycle tests use
a separate DB connection to distinguish uncommitted from committed state.

On the tested revision, the custom-action `ResourceChangedEvent` notification is
observed with no active transaction, published state visible to another connection,
and one persisted notification. A failed publication produces no successful
resource-change notification. The test observer writes its timing evidence to
`var/production-lifecycle.json`. That observation is specific to this custom-action
path; it does not establish post-commit guarantees for every generated operation,
outer transaction or distributed transaction. Ordinary lifecycle events and an
application's delivery guarantees must remain distinct. Reliable external delivery
requires an application-owned transactional outbox and delivery worker; a callback
alone cannot close the crash window between DB commit and an external send
(**INFRASTRUCTURE_LIMIT**).

## Read models

`ArticleSummary` remains the working public `ReadProjection::DTO` example backed
by Article with a field map, including scoped INDEX/SHOW. It is a read representation,
not another writable entity.

`AuthorPublishingStatistics` demonstrates a different contract: an application
SQL aggregate returned as a registered non-Doctrine resource through a custom GET
handler. Its route belongs to the aggregate's metadata, not to Author metadata;
`authorId` is a query input for the handler, not a resource-preloading `{id}`.
The handler checks ownership, groups draft/published counts, and uses
`#[NoTransaction]` for its read. It declares no generated CRUD operations.

**Open `CUSTOM-ACTION-READ-MODEL` (P2):** serialization currently tries to generate
a disabled SHOW route and returns 500. The test requires the two integer counts
and the aggregate's own type. No invented SHOW/persistence endpoint or custom
JSON:API controller is added to appease link generation. The needed public contract
is custom-route-only resource serialization with an appropriate canonical/self
link, or serialization without a generated SHOW link.

## Optimistic concurrency and Atomic Operations

`ConcurrencyAndAtomicTest` implements the two-client journey: GET ETag A, first
PATCH with A succeeds, second PATCH with A gets 412, and DB contains only the first
client's state. A second test starts independent HTTP kernels/DB connections and
holds UPDATE briefly to produce actual overlapping writes. It requires exactly
one 200 and one 412, and verifies the winner in DB. Both pass on the tested revision.
The existing mixed-manager validation case safely rejects with 409 before domain
validation; its original 422 assertion remains a classified infrastructure-boundary
contract (`ATOMIC-VALIDATION-BOUNDARY`). Existing torture concurrency, query shape
and cross-manager transaction assertions
are preserved; this small journey is not a replacement for them.

Atomic Operations remains generic add/update/remove resource and relationship
mutations. Authentication, authorization and protected input tests apply there too.
`op: publish` and an update `href` pointing at the publish action are rejected;
Atomic does not call custom command handlers. This is a deliberate supported
boundary, not a JSON:API violation or a new gap. A client needing atomic business
transitions uses an application-owned batch command with explicit authorization,
validation and transaction policy. It must not sneak publication into writable
state fields or invent new Atomic operation names.

## Developer experience and remaining contracts

The normal path uses resource metadata/groups, public data contracts/decorators,
Criteria, a filter interface/tag and custom handler context/result. Policies and
business operations are ordinary Symfony/Doctrine code. No bundle internals are
injected and no core JSON:API behavior is reimplemented.

Significant findings:

- **`EXTENSIBILITY-PROFILE-DI` (P2, DESIRED_CAPABILITY):** a registered public profile
  with required constructor injection is reported missing during container validation.
  Its diagnostic HTTP configuration remains executable and failing. The application
  uses public data decorators as its integration design; it does not modify the
  validator or introduce service-location to conceal the profile failure.
- **`WRITE-MODEL-SERIALIZER-METADATA` (P2, DESIRED_CAPABILITY):** attribute groups
  protect production input, but effective Symfony YAML write groups are not respected.
  External serializer mappings are normal framework integration, not a reason to
  replace generic resource processors or weaken the original type tests.
- Authorization requires separate processor and relationship decorators plus explicit
  command authorization. This works, but the public guide's older `ResourcePersister`
  example is not the installed `ResourceProcessor` contract, and its repository tag
  example differs from current registrations. Interface decoration is clearer here.
- Custom route guide examples describe automatic prefixing; literal paths on this
  revision need an explicit `/api`. This is a **DOCUMENTATION_GAP**, not an absent
  custom-action capability. Handler/interface documentation is more current than
  the controller-first custom-route guide.
- Profile documentation understates the four relationship-write hook methods and
  cannot make constructor DI work. Optional client-negotiated profiles should not
  be the sole authority for mandatory access policy.
- Read model metadata determines response serialization and links. A custom route
  returning a different shape must belong to that resource's metadata; an absent
  SHOW route still exposes the independent read-model gap described above.

Run normal production cases and all desired contracts separately if useful:

```bash
docker compose exec -T php php bin/phpunit tests/Acceptance/Production --exclude-group bundle-gap
docker compose exec -T php php bin/phpunit tests/Acceptance/Production --group bundle-gap
# Full run is expected to fail while open contracts remain:
docker compose exec -T -e ACCEPTANCE_RECORD_HTTP=1 php php bin/phpunit --log-junit var/acceptance-junit.xml
docker compose exec -T php php tools/acceptance-report.php
```

Gap metadata lives in `docs/bundle-gaps.json`; generated status/matrix/results cover
all acceptance tests. The [iteration report](production-iteration.md) maps each
scenario to files, public APIs, results and responsibility classification.

### Atomic route configuration

[Kernel](../src/Kernel.php) imports normal application routes and registers the public
AtomicController only when `jsonapi.atomic.enabled` is true, at
`jsonapi.atomic.endpoint`. This is normal Symfony application routing: changing the
endpoint must also change the path of the Atomic media channel if one is configured.
The HTTP disable/endpoint tests also check generated OpenAPI. Standard Atomic
resource mutations do not invoke arbitrary publishing commands; multi-connection
distributed transactions are not promised.

## Frozen profile semantics

| Option | Expected contract |
|---|---|
| `strategy: timestamp / boolean` | Deletion writes the configured timestamp or boolean field |
| `default_visibility: exclude / include / only` | Ordinary reads respectively omit deleted rows, retain all rows, or return only deleted rows |
| `query_flags` | Configured `with_deleted` / `only_deleted` names change visibility; obsolete hardcoded names do not |
| `delete_semantics: soft / hard` | Soft persists the deletion marker; hard removes the row |

The existing SoftDeleteConfigurationTest matrix freezes these semantics. Default audit CREATE/UPDATE is server policy and must work without client negotiation. Synchronous events are notifications, not exactly-once external delivery. `VersionResolverInterface` selects a representation; ETag version strategy selects cache/precondition validators.
