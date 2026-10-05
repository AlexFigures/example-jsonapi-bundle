# Public-feature consumer iteration

This application remains an independent Composer consumer. No bundle source or vendor file is patched. Desired assertions execute normally, including known failures. Application mistakes are fixed in the application; generic transport and extension defects stay executable bundle gaps.

This iteration is **not yet the exhaustive coverage requested for 1.0**. The [explicit feature inventory](feature-coverage.md) distinguishes verified contracts, variants that remain partial, configuration-only surfaces and untested interfaces. [Acceptance results](acceptance-results.json) identify the tested revision and current failures; historical observations in bundle-gaps.json are not current status.

## Latest verified run

Bundle revision `1a32d7b35ca5ce3e7c4500f52a2455655fa57c6b`: **588 test cases, 5,284 assertions, 578 passes and 10 classified failures**, corresponding to **seven open Acceptance IDs**. Zero unexpected failures or skips. Complete Torture: **62 cases, 4,177 assertions, 57 passes and five failures**, all PERFORMANCE-NPLUS1. See [the refresh report](bundle-refresh.md) for current observations and resolved historical IDs.

Atomic rejects independent connections with 409 before mutation, commits only the participating connection and rolls back same-connection failures. Composite-ID discovery now passes. The isolated profile fixture enables normal Symfony autowiring, and OpenAPI comparisons distinguish HTTP operations from legal Path Item metadata.

## Examples and independent evidence

| Extension | Application example | HTTP evidence |
|---|---|---|
| Filter/sort inheritance and exclusions | FeatureArticle author/editor metadata | Features/Filtering/InheritanceAndExtensionsTest |
| Multi-hop relationship alias | FeatureArticle → FeatureArticleTag → Tag | Features/Relationships/PathAliasTest |
| Custom operator | StartsWithOperator, jsonapi.filter.operator | Features/Filtering/InheritanceAndExtensionsTest |
| Custom sorting | TitleLengthSort and explicit MIN(tag.name) handler | Features/Sorting/AggregateSemanticsTest |
| Structural safeguards | Dedicated bounded/unlimited environments | Features/Filtering/StructuralLimitsTest, DisabledGuardsTest |
| Generated root pagination with joins | FeatureArticle association fixtures | Features/Relationships/RootJoinPaginationTest |
| Business/read custom handlers | FeatureCommandHandler, FeatureReadHandler | Features/CustomRoutes/HandlerContractTest |
| Scoped transactions | ScopedTransactionHandler capability-checks public interface | Features/CustomRoutes/HandlerContractTest |
| Ordinary Symfony response integration | FeatureCookbookController, JsonApiResponseFactory | Features/CustomRoutes/ResponseFactoryTest |
| DTO and CUSTOM read projections | FeatureSummary, FeatureCustomSummary, CookbookReadMapper | Features/Mapping/ConstructorAndProjectionTest |
| Operation input and representation version | FeatureArticleInput, FeatureVersionResolver | Features/Mapping/PublicInputAndVersionTest |
| Profile hooks | CookbookProfile | Features/Profiles/PublicHooksTest |
| Soft-delete configuration | Dedicated timestamp/boolean environments | Features/Profiles/SoftDeleteConfigurationTest, BooleanSoftDeleteTest |
| Resource/relationship events | Test-only HttpEventRecorder | Features/Events/ResourceEventsTest |
| Custom provider / typed repositories | MemoryArticleProvider, TypedMemoryRepository | Features/DataLayer/* |
| Media channels | MediaChannelController | Features/Protocol/MediaChannelsTest |
| OpenAPI and UI | Generated HTTP spec and all public endpoint attributes | Features/Docs/* |
| Combined production journeys | Existing publishing domain reused unchanged | Features/Composition/* |

Paths above are relative to tests/Acceptance; cookbook examples live under src. The [README cookbook](../README.md) links directly to files. The in-memory provider is deliberately a tiny deterministic test data source, reset between kernels, not a production persistence implementation.

## Responsibility boundary

Application code owns identities, ownership, query scope, article publication policy, aggregate sort semantics, search meaning, projection mapping and deterministic local side effects. The bundle owns generated-route integration, parsing/compiling configured operators, extension dispatch, transaction orchestration, relationship representation and consistent JSON:API errors. Custom providers implement public persistence contracts; they do not replace generated controllers.

Synchronous lifecycle events are observable notifications, not an exactly-once external delivery guarantee. Production lifecycle tests retain rollback checks. Reliable delivery to external services requires an application outbox or equivalent commit-aware infrastructure.

## Significant DX and documentation findings

- Attribute constructor options such as writeRequests and versionResolver need operation-specific onboarding and executable semantics. Registering a DTO or resolver alone does not establish that it is used; HTTP assertions verify that separately.
- Custom filter handlers and custom operators are different seams. Handler coverage cannot establish that a registered operator survives parser whitelisting and Doctrine compilation.
- To-many sorting is ambiguous. The application supplies MIN semantics explicitly; production reject policy remains enabled in its isolated environment.
- Legacy ResourcePersister examples differ from current ResourceProcessor configuration. Typed repository dispatch has a usable registration example; the other legacy typed contracts need a verified registration path before they can be advertised as supported.
- Resource-path configuration merges with defaults. A custom-provider environment must account for all discovered resource types and reject unsupported types explicitly.
- FetchPlanHook is independently verified: the cookbook profile requests counts and exposes them without enabling the built-in counts profile. ProfileContext and RelationshipBatchReaderInterface expose a relationship-read DTO marked internal; that annotation conflicts with the public extension signatures.
- Documentation endpoints are product endpoints too: native MIME negotiation, actual enabled operations, inherited whitelists, writable schemas, endpoint examples and configured pagination are independently asserted.
- JSON Schema and dx.* configuration must not be treated as working features merely because Symfony accepts their keys. The schema endpoint has an executable assertion; configuration-only tooling is explicitly recorded in the inventory.
- A service registered by class ID in an environment-specific package can be overwritten by the later application service resource. The aggregate sort example uses a distinct service ID with an explicit public tag; this is ordinary Symfony configuration, not a bundle gap.

## Remaining external coverage

This continuation added competing handler priorities, nested inheritance, shared/create/update validation groups, selective operations, duplicate/invalid discovery, audit identity and default write hooks, typed write dispatch, batch relationship loading, document budgets, cache disable/version/query/Last-Modified variants, error configuration, profile negotiation and Atomic endpoint/disable variants. Some pass; missing generic behavior is retained as executable gaps.

Further identifier, profile/count, media and cache combinations remain PARTIAL. The source audit in [configuration-dx-audit.md](configuration-dx-audit.md) identifies dormant typed relationship/write-mapper surfaces rather than implementing application dispatchers to conceal them. An explicit inventory status for every entry is not a claim that every variant is green or fully exercised.

Five composition journeys cover scoped authenticated search, profile/include/count/cache, DTO query/cache, authorized transactional publication/OpenAPI and UUID/lid/relationship Atomic rollback. Additional combinations remain possible and are not implied by those five tests.

## PR #67 evidence boundary

Normal acceptance covers preconditions, generated-ID/lid transactions, filter structural limits, relationship complexity, malformed UUID errors, joined-root pagination, projection order, identifier budgets, primary-resource exclusion from included, explicit include under never linkage, collection sort rejection/aggregate override, HEAD/OPTIONS and profile write hooks. Existing Production concurrency tests remain unchanged. Cross-manager/shard and large representation-loading evidence remains in tests/Torture.

The complete Torture suite is executed separately against disposable torture databases. Its current revision, all scenario classifications and bounded-query measurements are in [performance-results.json](performance-results.json). Composite-ID discovery, collection query growth and cross-manager late commit failure remain desired failing assertions; no distributed transaction promise is inferred.

## Open acceptance contracts

Current observations below supersede historical baselines. Full test/dataset evidence is in acceptance-results.json.

### VERSION-RESOLVER-CONTEXT — DESIRED_CAPABILITY, P2

Expected: VersionResolver receives negotiated profile and selects the configured DTO mapping.

Observed on tested revision: Negotiated DTO item and ordinary collection now return 500. Sparse collection and narrowly included representation tests pass; selection is no longer simply ignored.

Responsibility: Carry request ProfileContext into public representation-definition resolution.

Required external direction: Carry request ProfileContext into public representation-definition resolution.

- [testNegotiatedVersionChangesRepresentation](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php)

- [testVersionSelectionComposesAcrossCollectionsIncludesAndFields with data set "collection"](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php)

### DX-TYPED-PERSISTER-DISPATCH — DOCUMENTATION_GAP, P1

Expected: Documented jsonapi.persister tagged TypedResourcePersister implementations handle generated create routes independently for multiple resource types.

Observed on tested revision: Generated POST returns 500 for each of the two independently registered legacy typed persisters.

Responsibility: The bundle advertises the public legacy contract and tag; independent consumers need a supported typed write registration path or an explicit documented migration to ResourceProcessor. Reimplementing dispatch in the application would hide this mismatch.

Required external direction: Provide and document an active typed write contract/registration seam, or deprecate obsolete persister/tag examples with an executable migration example.

- [testDocumentedTypedPersisterRegistrationHandlesGeneratedWrites with data set "cards"](../tests/Acceptance/Features/DataLayer/TypedProviderTest.php)

- [testDocumentedTypedPersisterRegistrationHandlesGeneratedWrites with data set "notes"](../tests/Acceptance/Features/DataLayer/TypedProviderTest.php)

### CACHE-VERSION-STRATEGY — CONFIG_IMPLEMENTATION_GAP, P2

Expected: cache.etag.strategy=version uses X-Resource-Version supplied by the application; no version produces no ETag.

Observed on tested revision: strategy=version still produces a hash ETag; X-Resource-Version=7 is not used, and an ETag is still produced without a version.

Responsibility: Selecting the configured ETag generator belongs to bundle configuration, not application alias replacement.

Required external direction: Wire the version strategy through the public configuration and document the response-version header contract.

- [testConfiguredVersionStrategyUsesApplicationVersion with data set "version header"](../tests/Acceptance/Features/Cache/VersionStrategyTest.php)

- [testConfiguredVersionStrategyUsesApplicationVersion with data set "no version"](../tests/Acceptance/Features/Cache/VersionStrategyTest.php)

### PROFILE-DEFAULT-WRITE — DESIRED_CAPABILITY, P1

Expected: Per-type default profiles execute write hooks without an explicit Accept profile; application identity is recorded on create.

Observed on tested revision: Per-type default audit write still leaves createdBy null without explicit profile negotiation; explicitly negotiated audit identity works.

Responsibility: Default-profile selection must apply consistently to read and write lifecycle hooks. Applications should not require clients to request server audit policy.

Required external direction: Resolve default and per-type profiles before write-hook execution on generated mutations.

- [testPerTypeDefaultProfileAppliesToWriteHooksWithoutExplicitNegotiation](../tests/Acceptance/Features/Profiles/AuditIdentityTest.php)

### FILTER-HANDLER-LOGICAL-COMPOSITION — DESIRED_CAPABILITY, P1

Expected: A custom search predicate inside OR preserves the alternative ordinary filter branch.

Observed on tested revision: search=no-match OR views=10 returns no resources instead of the matching ordinary-filter branch.

Responsibility: The public imperative FilterHandlerInterface cannot naturally contribute a predicate at its AST position. Rebuilding logical query compilation in the application would replace generic bundle behavior.

Required external direction: Expose an expression/predicate custom-filter contract that composes at the original logical AST position, including bound parameters and error mapping.

- [testHandlerInsideOrPreservesAlternativeNormalPredicate](../tests/Acceptance/Features/Filtering/SearchCompositionTest.php)

### CACHE-COLLECTION-LAST-MODIFIED — CONFIG_IMPLEMENTATION_GAP, P2

Expected: collections_max_of=false disables automatically computed collection Last-Modified, while item validators remain available.

Observed on tested revision: collections_max_of=false still returns a collection Last-Modified header.

Responsibility: This is bundle-owned public configuration; implementing an application substitute would hide its missing behavior.

Required external direction: Honor the documented configuration or explicitly remove/deprecate the unsupported option with a migration contract.

- [testDisablingCollectionMaximumDoesNotSynthesizeCollectionValidator](../tests/Acceptance/Features/Cache/DisabledCollectionLastModifiedTest.php)

### PROFILE-AUDIT-META — CONFIG_IMPLEMENTATION_GAP, P2

Expected: expose_in_meta=true exposes server-owned audit information in negotiated resource metadata.

Observed on tested revision: Negotiated audit creation succeeds, but resource meta is absent with expose_in_meta=true.

Responsibility: This is bundle-owned public configuration; implementing an application substitute would hide its missing behavior.

Required external direction: Honor the documented configuration or explicitly remove/deprecate the unsupported option with a migration contract.

- [testConfiguredAuditMetaIsExposedOnNegotiatedRepresentation](../tests/Acceptance/Features/Profiles/AuditIdentityTest.php)
