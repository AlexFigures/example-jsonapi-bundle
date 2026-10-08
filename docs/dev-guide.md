# Dev guide

Use this path to integrate the bundle into a Symfony application. The [cookbook](cookbook.md) links each extension family to a small implementation and consumer HTTP test. Use [release workflow](compatibility-workflow.md) to verify a bundle version, and [Torture](../TORTURE.md) for the separate fault laboratory.

## 1. Install and prepare the example

After cloning, run `bash tools/dev-setup.sh` from the repository root. It builds the PHP CLI image, installs `composer.lock`, creates demo/test/torture databases, and seeds the **disposable demo databases**. Seeding drops/recreates their mapped schemas. Tests reset their own disposable schemas before each case. No host PHP, Composer or Node is needed.

The default runtime is PHP 8.2 / Symfony 7.4. Export `APP_PORT=18080` if 8080 is occupied, and use that port in the HTTP examples below. Export `ACCEPTANCE_SUBNET` to choose another unused subnet. Compose services supply the PostgreSQL/MySQL URLs; overrides belong in `.env.local` or `.env.test.local`. Do not run suites concurrently against the same test databases.

```bash
docker compose exec -T php composer validate --strict
docker compose exec -T php php bin/console about
docker compose exec -T php php bin/console debug:router
docker compose exec -T php php tools/dev-http-smoke.php
```

Swagger is `/_jsonapi/docs`, its JSON is `/_jsonapi/openapi.json`, and JSON Schema is `/_jsonapi/schemas`. Documentation is enabled by the bundle's defaults. Compose uses [router.php](../public/router.php) so dotted URLs reach Symfony Runtime. `deprecated: true` in the test-only OpenAPI cookbook verifies OpenAPI metadata; those controller routes are excluded from `dev`/`prod`.

## 2. Register a resource

Start with [Author](../src/PgEntity/Author.php), then [Article](../src/PgEntity/Article.php). Place Doctrine entities in the namespace mapped to their entity manager: `App\PgEntity` for PostgreSQL and `App\MysqlEntity` for MySQL. [Doctrine configuration](../config/packages/doctrine.yaml) defines both managers; [bundle configuration](../config/packages/jsonapi.yaml) lists discovered resource directories, and [routes.yaml](../config/routes.yaml) imports generated routes.

`#[JsonApiResource(type: 'articles')]` establishes public identity. `#[Id]`, `#[Attribute]` and `#[Relationship]` describe JSON:API fields; Doctrine attributes describe storage. Resource operation metadata can restrict generated CRUD, as in [AuditLog](../src/PgEntity/AuditLog.php). Cross-database comments use a scalar `articleId`; a shared identifier does not imply a cross-manager transaction.

The setup command prints named fixture IDs. A first read works without manually copying one:

```bash
curl --fail-with-body -g 'http://localhost:8080/api/articles?include=author,tags&fields[articles]=title,author,tags&sort=title,id&page[size]=2' \
  -H 'Accept: application/vnd.api+json' -H 'Authorization: Bearer reader'
```

## 3. Make writes safe

Article uses `Symfony\Component\Serializer\Attribute\Groups` with `articles:read` and `articles:write`. Only client-owned attributes and permitted relationships are writable. `status`, timestamps and views remain server-owned; the domain method controls publication. Symfony validation supplies field constraints. [WriteInputTest](../tests/Acceptance/Production/WriteSurfaceTest.php) and [PublicInputAndVersionTest](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php) exercise protected fields, distinct CREATE/UPDATE inputs, partial PATCH and Atomic rollback. The historical broad serializer mapping under `config/acceptance-serializer` belongs only to protocol tests.

For a resource needing input models, follow [FeatureArticle](../src/PgEntity/FeatureArticle.php), [FeatureArticleInput](../src/Api/Cookbook/FeatureArticleInput.php) and [FeatureArticleUpdateInput](../src/Api/Cookbook/FeatureArticleUpdateInput.php). Preserve omitted fields on PATCH; a missing value differs from explicit null. A custom [WriteMapper](../src/JsonApi/DataLayer/CookbookWriteMapper.php) is another public seam.

Create a reference resource using the admin demo credential:

```bash
curl --fail-with-body 'http://localhost:8080/api/authors' \
  -H 'Authorization: Bearer admin' -H 'Accept: application/vnd.api+json' \
  -H 'Content-Type: application/vnd.api+json' \
  -d '{"data":{"type":"authors","attributes":{"name":"Guide Author","email":"guide@example.test"}}}'
```

## 4. Apply authorization and query scope

[PublishingAuthentication](../src/Security/PublishingAuthentication.php) authenticates `/api/` requests. Demo credentials are `reader`, `editor-a`, `editor-b` and `admin`. [PublishingContext](../src/Security/PublishingContext.php) resolves a request-local identity; replace the deterministic credential adapter in a real application.

[ArticlePolicy](../src/Security/ArticlePolicy.php) owns read/edit/delete/associate/publish decisions. [ScopedArticleRepository](../src/JsonApi/ScopedArticleRepository.php) decorates `ResourceRepository` so collection, SHOW and projection reads obey ownership. [AuthorizedArticleProcessor](../src/JsonApi/AuthorizedArticleProcessor.php) protects generated mutations; [AuthorizedArticleRelationships](../src/JsonApi/AuthorizedArticleRelationships.php) protects relationship targets and operations. [Service registration](../config/services.yaml) wires these public interfaces.

Scope must survive filters, includes, relationship endpoints and projections. Authorizing a source does not authorize every target. Mandatory security is server policy; an optional negotiated profile must not turn it off. Read the [HTTP scope](../tests/Acceptance/Production/QueryScopeTest.php) and [relationship authorization](../tests/Acceptance/Production/RelationshipAuthorizationTest.php) tests.

## 5. Extend filters and sorts

Declare a whitelist with `FilterableFields`/`SortableFields`; relationship inheritance expands permitted paths. [ArticleSearchFilter](../src/JsonApi/Filter/ArticleSearchFilter.php) composes custom predicates with existing Criteria and scope. [StartsWithOperator](../src/JsonApi/Filter/StartsWithOperator.php) adds an operator; [TitleLengthSort](../src/JsonApi/Sort/TitleLengthSort.php) adds a scalar sort. Register custom operators/handlers with `jsonapi.filter.operator`, `jsonapi.filter.handler`, `jsonapi.sort.handler`.

```bash
curl --fail-with-body -g 'http://localhost:8080/api/articles?filter[search][eq]=Symfony&sort=title-length,id' \
  -H 'Accept: application/vnd.api+json' -H 'Authorization: Bearer reader'
```

The HTTP AST supports AND/OR. To-many sorting needs an explicit policy and deterministic aggregate semantics, demonstrated by [MinimumTagNameSort](../src/JsonApi/Sort/MinimumTagNameSort.php). Keep stable root pagination and a tie-breaker. Query budgets and structural guards are configured independently of application visibility rules.

## 6. Publish through a business command

Article declares a literal `/api/articles/{id}/publish` custom route and [PublishArticle](../src/Application/Article/PublishArticle.php) implements `CustomRouteHandlerInterface`. It receives `CustomRouteContext`, authorizes the operation, invokes the domain transition, flushes through Doctrine and returns `CustomRouteResult`. [Registration](../config/services.yaml) uses `jsonapi.custom_route_handler`.

Read [PublishArticleTest](../tests/Acceptance/Production/PublishArticleTest.php) for the complete CREATE → PATCH → publish journey. With an article ID printed by setup, use the real fixture ID rather than assuming `1`:

```bash
ARTICLE_ID=REPLACE_WITH_ARTICLE_1_ID_FROM_SETUP
curl --fail-with-body -X POST "http://localhost:8080/api/articles/$ARTICLE_ID/publish" \
  -H 'Authorization: Bearer editor-a' -H 'Accept: application/vnd.api+json' \
  -H 'Content-Type: application/vnd.api+json'
```

Draft with content publishes; already published is a sequential no-op; archived returns 409, blank content 422, missing ID 404, unauthorized actor 403. Custom-route-only read models such as [AuthorPublishingStatistics](../src/Api/AuthorPublishingStatistics.php) declare their own resource metadata and do not invent CRUD routes.

## 7. Handle events and transaction boundaries

[ArticlePublished](../src/Application/Article/ArticlePublished.php) is a domain event. [PublicationRecorder](../src/EventSubscriber/PublicationRecorder.php) persists a local notification inside the same PostgreSQL transaction. [LifecycleTest](../tests/Acceptance/Production/LifecycleTest.php) verifies rollback and visibility through another connection. [ResourceEventsTest](../tests/Acceptance/Features/Events/ResourceEventsTest.php) verifies the bundle's resource and relationship notifications.

The domain event fires before commit. Local persistence may participate in the transaction; external delivery needs an application-owned transactional outbox and worker. A callback alone does not prove exactly-once delivery or every outer-transaction timing guarantee. `[NoTransaction]` is appropriate for the bounded read-handler example in [FeatureReadHandler](../src/Api/Cookbook/FeatureReadHandler.php).

## 8. Add projections, profiles and custom adapters

[ArticleSummary](../src/Api/ArticleSummary.php) demonstrates a scoped DTO projection and field map. [CookbookReadMapper](../src/JsonApi/DataLayer/CookbookReadMapper.php) demonstrates CUSTOM projection. [FeatureVersionResolver](../src/Api/Cookbook/FeatureVersionResolver.php) selects negotiated representations; it does not resolve entity concurrency versions. [CookbookProfile](../src/JsonApi/Profile/CookbookProfile.php) demonstrates DI and public hooks registered with `jsonapi.profile`. Read [projection tests](../tests/Acceptance/Features/Mapping/ConstructorAndProjectionTest.php) and [profile tests](../tests/Acceptance/Features/Profiles/PublicHooksTest.php).

For non-Doctrine storage use [MemoryArticleProvider](../src/JsonApi/DataLayer/MemoryArticleProvider.php) and its [isolated configuration](../config/packages/features_memory/config.yaml). Typed repositories/persisters select resource types through `supports(type)` and tags. [TypedMemoryRelationships](../src/JsonApi/DataLayer/TypedMemoryRelationships.php) selects relationship endpoint handlers by **source type**. [Two-type HTTP proof](../tests/Acceptance/Features/DataLayer/TypedRelationshipTest.php) covers all reader/updater methods, autoconfiguration, explicit tags and fallback. Custom writes also need `ExistenceChecker` and a transaction capability matching the storage semantics.

Endpoint adapters do not automatically preload representation relationships or execute profile hooks. [SuggestedAuthorsBatchReader](../src/JsonApi/DataLayer/SuggestedAuthorsBatchReader.php) and [batch reader tests](../tests/Acceptance/Features/DataLayer/BatchRelationshipReaderTest.php) demonstrate the separate public batch-loading capability and structural query budget.

## 9. Apply Atomic and concurrency rules

[Kernel](../src/Kernel.php) registers AtomicController at the configured endpoint only when enabled. Its media channel path must match that endpoint. Send `application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"` for both request and response:

```bash
curl --fail-with-body 'http://localhost:8080/api/operations' \
  -H 'Authorization: Bearer admin' \
  -H 'Content-Type: application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"' \
  -H 'Accept: application/vnd.api+json;ext="https://jsonapi.org/ext/atomic"' \
  -d '{"atomic:operations":[{"op":"add","data":{"type":"authors","attributes":{"name":"Atomic Guide Author","email":"atomic-guide@example.test"}}}]}'
```

Atomic supports generic resource/relationship add/update/remove, not a custom `publish` operation. A batch crossing independent database connections rejects before mutation; there is no distributed transaction promise. Same-connection failures roll back earlier writes. [ConcurrencyAndAtomicTest](../tests/Acceptance/Production/ConcurrencyAndAtomicTest.php) exercises stale ETags and overlapping writes: one writer succeeds, the other receives 412. Fetch the current ETag and send `If-Match` when updating the protected resource.

## 10. Run tests and generate evidence

```bash
docker compose exec -T php composer test:production:full
docker compose exec -T php composer test:features
docker compose exec -T php php bin/phpunit tests/Acceptance/Features/DataLayer/TypedRelationshipTest.php
python3 -m unittest discover -s tests/Tooling
python3 tools/release-gate.py --run --clean-install
```

The common gate runs the complete Acceptance set, including both subsets, and full Torture. Generated evidence records runtime/package versions, exact bundle revision, source/lock digests, fresh-install identity and report hashes. [Release gate](release-gate.md) and [compatibility matrix](compatibility-matrix.md) show current execution; [history](history/README.md) retains earlier closed findings. Optional forward fixtures and stabilization commits cannot substitute for a published RC/final release proof.
