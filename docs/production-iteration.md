# Production onboarding iteration

## Latest acceptance refresh

Tested bundle `727d671c47a6de33340939415ef93053fdcbaf7d` with a complete
HTTP acceptance run: **391 cases, 3,781 assertions, 379 passing, 12 classified
failures, zero unexpected failures and zero skips**. Production remains 70/75
passing. Compared with the initial iteration, no gap closed and no new failing
scenario appeared. The initial iteration results below are historical.

Remaining desired bundle capabilities: `QUERY-002` (one failure),
`QUERY-SCOPE-GRAPH` (three), `EXTENSIBILITY-PROFILE-DI` (one),
`CUSTOM-ACTION-READ-MODEL` (one), and `WRITE-MODEL-SERIALIZER-METADATA` (five).
`ATOMIC-VALIDATION-BOUNDARY` remains a separate infrastructure-limit contract
(one failure): safe boundary rejection returns 409 before the expected 422 domain
validation; the rollback assertion still passes.

Generated status/matrix/results have been refreshed; assertions and application
code are unchanged. This refresh covers Acceptance, not a new full Torture or
performance run.

This repository now separates a small publishing reference application, HTTP
acceptance contracts and the existing torture laboratory. Composer supplies the
bundle; no installed implementation is patched, no generated CRUD controller is
replaced, and no generic JSON:API compatibility engine is added.

## Implemented scenarios

All tests below run through Symfony routing/kernel. Fixture setup and persisted
outcome inspection use Doctrine; concurrent clients run independent HTTP kernels.

| Scenario | Main application/configuration files | Public bundle API | Executable result |
|---|---|---|---|
| A: publish | `src/PgEntity/Article.php`, `src/Application/Article/PublishArticle.php`, `PublicationRejected.php`, `ArticlePublished.php` | `JsonApiCustomRoute(handler:)`, `CustomRouteHandlerInterface`, `CustomRouteContext`, `CustomRouteResult` | Green: successful representation/DB state, repeated success, archived conflict, missing-content validation, unknown/malformed IDs, media negotiation |
| B: authentication/authorization | `src/Security/PublishingAuthentication.php`, `PublishingContext.php`, `PublishingIdentity.php`, `ArticlePolicy.php`, `PublishingRules.php`; `config/services.yaml`, `config/packages/publishing_policy.yaml` | Generated routes; decorators of `ResourceRepository`, `ResourceProcessor`, `RelationshipUpdater`; public HTTP error objects | Green: reader/editor/admin permissions, foreign ownership, reference-resource protection, JSON:API errors, Atomic denial and rollback |
| C: mandatory collection scope | `src/JsonApi/ScopedArticleRepository.php`, `src/Security/PublishingRules.php` | `ResourceRepository`, `Criteria::customConditions` | Root collection, SHOW, DTO projection, query composition and override resistance green; graph reads fail `QUERY-SCOPE-GRAPH` |
| D: protected relationships | `src/JsonApi/AuthorizedArticleRelationships.php`, `AuthorizedArticleProcessor.php`, `src/Security/ArticlePolicy.php` | `RelationshipUpdater`, `ResourceProcessor`, `ChangeSet`, `ResourceIdentifier` | Green: tag add/remove, target existence, author/admin distinction, self-editor rule, embedded/Atomic bypass rejection |
| E: lifecycle/side effects | `src/EventSubscriber/PublicationRecorder.php`, `src/PgEntity/PublicationNotification.php`; test-only `LifecycleProbe.php` and publishing service configuration | `ResourceChangedEvent`; custom handler transaction contract | Green: one local record, no business-failure side effect, rollback of state/record, observable lifecycle event; application event before flush/commit |
| F: application search | `src/JsonApi/Filter/ArticleSearchFilter.php`, Article metadata, service tag | `FilterHandlerInterface`, `FilterableField(customHandler:)`, `jsonapi.filter.handler` | Green: title/content, filter/sort/page/scope composition, invalid terms, parameterized values and literal wildcard handling |
| G: read models | Existing `src/Api/ArticleSummary.php`; new `AuthorPublishingStatistics.php` and `src/Application/Article/AuthorPublishingStatisticsHandler.php` | `ReadProjection::DTO`; custom handler, `NoTransaction`, custom-route-only resource metadata | Existing projection green and scoped; aggregate authorization/unknown input/no mutation routes green; representation fails `CUSTOM-ACTION-READ-MODEL` |
| H: safe writable surface | Article read/write groups and timestamps; isolated `config/acceptance-serializer/Article.yaml`, `config/packages/test/serializer.yaml` | `JsonApiResource::denormalizationContext`, serializer groups | Production create/PATCH/Atomic protected inputs green; original broad-surface assertions reveal `WRITE-MODEL-SERIALIZER-METADATA` |
| I: optimistic concurrency | `tests/Acceptance/Production/ConcurrencyAndAtomicTest.php`, `Support/ConcurrentPublishingRequests.php`, `tools/publishing-worker.php` | Configured ETags and `If-Match` on generated PATCH | Sequential and genuinely overlapping two-writer journeys green; only winner remains in DB |
| J: command idempotence | `Article::publish`, publish handler and recorder | Custom handler/result | Green: repeated publication succeeds without replacing timestamp or repeating the application event/record; generic Idempotency-Key support is not claimed |
| Atomic boundary | Production authorization/relationship/write/concurrency HTTP tests | Public Atomic endpoint/configuration and standard operation grammar | Green: standard operations enforce policies; custom publish op/href rejected; business commands are not invented Atomic extensions |
| Profile DI/DX | `tests/Acceptance/Support/InjectableProfile.php`, `config/packages/publishing_di/base.yaml`, `Production/ExtensibilityTest.php` | `ProfileInterface`, profile descriptor/requirements and `jsonapi.profile` tag | Desired HTTP boot assertion fails `EXTENSIBILITY-PROFILE-DI` |

The `publishing` test environment has the normal safe contract and disposable test
databases. Historical protocol tests retain their desired broad input mapping in
`test`. Its five now-failing write/workflow assertions have not been weakened to
assert read-only rejection. Test and torture environments explicitly disable the
normal publishing policy; torture assertions/topology/performance contracts stay
in their existing suites.

## Green production contracts

`tests/Acceptance/Production` covers the whole editor journey: scoped search,
draft creation, editing, publish, persisted representation, local event side effect
and idempotent repeat. It also covers role/ownership/association checks on generated
CRUD and relationship routes; Atomic authorization and batch rollback; protected
server fields; the existing projection; media/error mapping; DB rollback; and
sequential/overlapping If-Match writes. Green means those assertions execute and
pass, not that the entire example has a complete security boundary: the graph-scope
gap remains an explicit P1 limitation.

## New bundle gaps

Each gap has a normal failing assertion, `bundle-gap` group, stable marker and
`docs/bundle-gaps.json` metadata. None is skipped or asserts observed bad behavior.
All four are **DESIRED_CAPABILITY**, not JSON:API conformance allegations.

| ID / severity | Expected versus observed | Responsibility / required external direction | Failing tests |
|---|---|---|---|
| `QUERY-SCOPE-GRAPH` / P1 | Foreign Article objects and identifiers must be absent from related collections, includes and linkage. All three paths currently leak them despite scoped root reads. | Visibility is application policy. A single mandatory scope/visibility seam across all generated query/graph/identifier paths belongs to the bundle. Scope must run before pagination; replacing graph traversal or filtering hydrated pages is not the solution. | `QueryScopeTest::testRelatedCollectionCannotLeakForeignArticles`, `testIncludesCannotLeakForeignArticles`, `testRelationshipLinkageCannotLeakForeignIdentifiers` |
| `EXTENSIBILITY-PROFILE-DI` / P2 | A tagged public profile with a constructor-injected context should boot. Container validation instead says the registered profile does not exist. | Public Symfony service integration must accept ordinary dependency injection. Required external contract: validation/discovery of injectable profiles without unconfigured construction of application services. | `ExtensibilityTest::testPublicProfileSupportsConstructorInjectedUserContext` |
| `CUSTOM-ACTION-READ-MODEL` / P2 | A custom-route-only aggregate resource should serialize its type/counts. Serialization instead generates a nonexistent disabled SHOW route and returns 500. | Aggregate SQL/policy belongs to the application; representation/link construction belongs to the bundle. Needed: canonical custom resource links or serialization without a generated SHOW route. No fictitious CRUD endpoint is introduced. | `ReadModelTest::testAggregateResourceIsIndependentOfDoctrineWriteModel` |
| `WRITE-MODEL-SERIALIZER-METADATA` / P2 | Effective Symfony YAML write groups should compose with attribute/resource context. Symfony reports configured write groups, but bundle input validation rejects the fields with 422. | External serializer mappings are a normal Symfony integration. Use effective serializer metadata consistently rather than requiring consumers to duplicate transport metadata or rewrite generic processors. | Existing `DoctrineTypesTest::testWriteTypesRoundTrip`, `ResourceCreateTest::testAllWritableAttributesAndRelationships`, `ResourceUpdateTest::testAttributesAndRelationshipsTogether`, `RealWorldWorkflowTest::testPublishingWorkflow`, `FieldAliasesTest::testReadAndWriteAlias` |

An additional existing assertion is classified separately as
`ATOMIC-VALIDATION-BOUNDARY` (**INFRASTRUCTURE_LIMIT**, P2): the original mixed-manager
batch still requires 422 for invalid Article title and verifies no preceding Comment
write persists. The installed single-manager provider safely rejects the boundary
with 409 before domain validation. Rollback/state assertions pass; the original
422 assertion remains failing. This is not a newly alleged transaction-safety defect
or a request for distributed commit. The inventory records that error-precedence
boundary explicitly rather than weakening the prior executable contract.

Original gaps and assertion strengths remain in the inventory. Current open/resolved
status is generated from a complete suite, not inferred from historical descriptions.
No internal bundle patch design is proposed; these are consumer-facing contracts.

## Application responsibilities confirmed

**APPLICATION_POLICY:** deterministic credential adapter; authenticated identity;
which operations each role/owner may perform; allowed associations; draft publishing
conditions; archived conflict and idempotent repeat; bounded title/content search;
aggregate query/ownership; server timestamps; local publication records. These need
application code and are not gaps merely because the bundle does not choose them.

**INFRASTRUCTURE_LIMIT:** external notifications cannot be made exactly-once merely
by a callback after a DB commit. The local recorder shares the publication transaction.
Reliable external delivery needs an application outbox/worker. The example does not
claim distributed transactions or generic Idempotency-Key replay support.

The application domain event is deliberately before flush/commit. A separate DB
observer currently sees the bundle's custom-action ResourceChangedEvent after
commit, including the persisted recorder; failed publication produces no successful
notification. This measured custom-action observation is not an inferred guarantee
for every bundle lifecycle event, outer transaction or storage topology.

## Developer experience

Normal integration uses public resource metadata/groups, three data-contract
decorators, Criteria, a custom filter interface/tag and a custom handler context/result.
It injects no internal bundle service. The separate processor/relationship/query
policy seams work for the tested root operations, but uniform graph visibility is
still missing. Constructor DI cannot be used in the documented profile path.

**DOCUMENTATION_GAP:** current guide examples are controller-first, imply custom
route prefixing, and still present older ResourcePersister/tag examples. This revision
requires literal `/api` paths and uses ResourceProcessor. The handler API docs are
more useful. Profile relationship hook documentation is incomplete. Read model
metadata must belong to the response type, and custom-route-only resources also
need a discoverable link contract. These findings are recorded without labeling
working ordinary application responsibilities as runtime bundle defects.

## Documentation and validation

- README now leads with three audience paths and the short normal integration file sequence.
- `docs/production-example.md` walks through authentication, safe writes, decorators,
  scope/search, publish, lifecycle/rollback, projections, concurrency and Atomic boundaries.
- `ACCEPTANCE.md` explains production versus historical protocol environments and classification.
- `docs/bundle-gaps.json` and the existing report generator include the new executable gaps.
- Generated `acceptance-results.json`, matrix and status provide the tested Composer
  revision, PHP/PHPUnit versions, individual results, HTTP observations and exact counts.

Full validation: **391 cases, 3,784 assertions; 379 pass and 12 classified failures,
no skips**. Of the 75 new production cases, 70 pass and five expose the three
production gaps. Five historical write cases expose external serializer metadata;
the remaining two failures are filter-depth (`QUERY-002`) and the separately
classified mixed-manager validation boundary. Tested dependency: `cbbd06106a3353f5e70b89feac48f3e13df3384e`,
PHP 8.4.26 / PHPUnit 11.5.56. One existing torture graph test passes (27 assertions).

Validation commands: full HTTP acceptance with JUnit and HTTP traces; production
contracts through the same full run; Composer validation without dependency updates;
PHP syntax checks; `git diff --check`; one existing torture graph test on separate
torture databases to check environment isolation. Large torture/performance suites
are not claimed to have been rerun by this iteration.
