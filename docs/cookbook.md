# Feature cookbook

Each row links application code to executable consumer HTTP assertions. Normal publishing examples run in `dev`/`prod`; `features_*` environments isolate optional metadata and adapters on disposable test databases. Cookbook controller routes are loaded only in test environments.

| Area | Application example | HTTP contract |
|---|---|---|
| Built-in filtering | [Article](../src/PgEntity/Article.php) | [operators](../tests/Acceptance/Query/FilteringTest.php) |
| Filter/sort inheritance and exclusions | [FeatureArticle](../src/PgEntity/FeatureArticle.php) | [inheritance](../tests/Acceptance/Features/Filtering/InheritanceAndExtensionsTest.php) |
| Custom search / operator | [ArticleSearchFilter](../src/JsonApi/Filter/ArticleSearchFilter.php), [StartsWithOperator](../src/JsonApi/Filter/StartsWithOperator.php) | [scope/search](../tests/Acceptance/Production/QueryScopeTest.php), [operator contracts](../tests/Acceptance/Features/Filtering/InheritanceAndExtensionsTest.php) |
| Custom / aggregate sort | [TitleLengthSort](../src/JsonApi/Sort/TitleLengthSort.php), [MinimumTagNameSort](../src/JsonApi/Sort/MinimumTagNameSort.php) | [to-many policy](../tests/Acceptance/Features/Sorting/CollectionPolicyTest.php), [aggregate semantics](../tests/Acceptance/Features/Sorting/AggregateSemanticsTest.php) |
| Join-entity path alias | [FeatureArticleTag](../src/PgEntity/FeatureArticleTag.php) | [aliases](../tests/Acceptance/Features/Relationships/PathAliasTest.php) |
| Relationship policies / writes | [Article](../src/PgEntity/Article.php) | [linking policies](../tests/Acceptance/Features/Relationships/LinkingPoliciesTest.php), [writes](../tests/Acceptance/Production/RelationshipPolicyTest.php) |
| Relationship 403 / error type links | [contracts and responsibility boundary](relationship-authorization-and-error-links.md) | [authorization and Atomic rollback](../tests/Acceptance/Production/RelationshipAuthorizationTest.php), [server-default hook](../tests/Acceptance/Features/Relationships/DefaultAuthorizationHookTest.php), [optional error links](../tests/Acceptance/Protocol/ErrorTypeLinksTest.php) |
| Business handler | [PublishArticle](../src/Application/Article/PublishArticle.php) | [publish](../tests/Acceptance/Production/PublishArticleTest.php) |
| NoTransaction / CriteriaBuilder | [FeatureReadHandler](../src/Api/Cookbook/FeatureReadHandler.php) | [custom-route contracts](../tests/Acceptance/Features/CustomRoutes/HandlerContractTest.php) |
| ResponseFactory / OpenAPI attributes | [FeatureCookbookController](../src/Controller/FeatureCookbookController.php) | [response forms](../tests/Acceptance/Features/CustomRoutes/ResponseFactoryTest.php), [OpenAPI](../tests/Acceptance/Features/Docs/OpenApiTest.php) |
| DTO / CUSTOM read mapper | [FeatureSummary](../src/Api/FeatureSummary.php), [CookbookReadMapper](../src/JsonApi/DataLayer/CookbookReadMapper.php) | [projections](../tests/Acceptance/Features/Mapping/ConstructorAndProjectionTest.php) |
| Profiles / representation version | [CookbookProfile](../src/JsonApi/Profile/CookbookProfile.php), [FeatureVersionResolver](../src/Api/Cookbook/FeatureVersionResolver.php) | [hooks](../tests/Acceptance/Features/Profiles/PublicHooksTest.php), [version/input contracts](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php) |
| Resource events | [PublicationRecorder](../src/EventSubscriber/PublicationRecorder.php) | [CRUD/relationship events](../tests/Acceptance/Features/Events/ResourceEventsTest.php) |
| Custom / typed data layer | [MemoryArticleProvider](../src/JsonApi/DataLayer/MemoryArticleProvider.php), [TypedMemoryRepository](../src/JsonApi/DataLayer/TypedMemoryRepository.php) | [provider](../tests/Acceptance/Features/DataLayer/CustomProviderTest.php), [typed dispatch](../tests/Acceptance/Features/DataLayer/TypedProviderTest.php) |
| Scoped transactions | [ScopedTransactionHandler](../src/Api/Cookbook/ScopedTransactionHandler.php) | [manager boundary](../tests/Acceptance/Features/CustomRoutes/HandlerContractTest.php) |
| Optimistic concurrency / Atomic | [production walkthrough](dev-guide.md) | [concurrency](../tests/Acceptance/Production/ConcurrencyAndAtomicTest.php), [Atomic configuration](../tests/Acceptance/Features/Atomic/ConfigurationMatrixTest.php) |
| Competing handlers / deep inheritance | [PriorityFilter](../src/JsonApi/Filter/PriorityFilter.php), [PrioritySort](../src/JsonApi/Sort/PrioritySort.php) | [priority](../tests/Acceptance/Features/Filtering/HandlerPriorityTest.php), [nested inheritance](../tests/Acceptance/Features/Filtering/NestedInheritanceTest.php) |
| Required constructor / validation groups | [FeatureMemo](../src/PgEntity/FeatureMemo.php) | [validation matrix](../tests/Acceptance/Features/Mapping/SharedValidationGroupsTest.php) |
| Audit identity / defaults | [AuditIdentity](../src/Security/AuditIdentity.php) | [audit contracts](../tests/Acceptance/Features/Profiles/AuditIdentityTest.php) |
| Computed relationship batch loading | [SuggestedAuthorsBatchReader](../src/JsonApi/DataLayer/SuggestedAuthorsBatchReader.php) | [batch reader](../tests/Acceptance/Features/DataLayer/BatchRelationshipReaderTest.php) |
| Selective operations / discovery | [FeatureRecord](../src/PgEntity/FeatureRecord.php) | [operations](../tests/Acceptance/Features/Mapping/SelectiveOperationsTest.php), [boot discovery](../tests/Acceptance/Features/Configuration/ResourceDiscoveryTest.php) |
| Cache configuration | [isolated environments](../config/packages/features_cache) | [Last-Modified](../tests/Acceptance/Features/Cache/LastModifiedConfigurationTest.php), [version ETag contract](../tests/Acceptance/Features/Cache/VersionStrategyTest.php) |
| Feature combinations | [publishing application](dev-guide.md) | [composition journeys](../tests/Acceptance/Features/Composition) |

| Typed relationship endpoints | [TypedMemoryRelationships](../src/JsonApi/DataLayer/TypedMemoryRelationships.php), [registration](../config/packages/features_typed_relationships/config.yaml) | [two-type reads/writes, tags and fallback](../tests/Acceptance/Features/DataLayer/TypedRelationshipTest.php) |


Run `docker compose exec -T php composer test:features` for the full feature set, or `docker compose exec -T php php bin/phpunit tests/Acceptance/Features/DataLayer/TypedRelationshipTest.php` for the typed endpoint adapter. These are bounded contracts, not claims about every combination. Full public inventory: [feature coverage](feature-coverage.md).
