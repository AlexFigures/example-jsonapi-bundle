# Подтверждённые гэпы для реализации в бандле

Проверенная ревизия: `8750b80831dba484f3de0ff55c345fec5fdc29e0`.

Источник: полный независимый прогон приложения; [current-gaps.json](current-gaps.json) содержит только актуальные наблюдаемые гэпы.

Acceptance: {'PASS': 644, 'FAIL': 21, 'SKIP': 0}. Torture: {'PASS': 62}. Новых неклассифицированных регрессий: 0.

Feature inventory: {'COVERED_GREEN': 302, 'COVERED_GAP': 27, 'CONFIG_ONLY': 17, 'DOCUMENTATION_ONLY': 2, 'NOT_APPLICABLE': 1}. PARTIAL — 0; ограниченные доказанные контракты описаны в [feature-review.json](feature-review.json).

## DOCS-EXPOSE-ID-CONTRACT — P1 / DOCUMENTATION_GAP

**Ожидается:** Read resource OpenAPI keeps id required/non-null even if exposeId=false; transport identity remains valid.

**Наблюдается:** HTTP returns valid id but schema omits id from required.

**Внешний контракт для бандла:** Read resource OpenAPI keeps id required/non-null even if exposeId=false; transport identity remains valid.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\ResourceOptionsTest::testExposeIdFalseDoesNotMakeProtocolIdentityOptionalInDocumentation`

## DOCS-METADATA-CONTRACT — P1 / DX_GAP

**Ожидается:** Default ResourceMetadata implementation fulfills the published ResourceMetadataInterface contract.

**Наблюдается:** The concrete default ResourceMetadata does not implement the public interface promised by its API documentation.

**Внешний контракт для бандла:** Make the default metadata implementation conform to its published interface or correct/remove the claim and provide a supported implementation seam.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\RegistryContractTest::testDocumentedDefaultMetadataImplementsPublicMetadataContract`

## DX-PUBLIC-SIGNATURE-INTERNAL-DTO — P1 / DX_GAP

**Ожидается:** Consumer-facing extension contracts use public stable DTOs or public supported replacements.

**Наблюдается:** Batch-reader and custom-route registry signatures expose types marked @internal.

**Внешний контракт для бандла:** Promote stable DTOs or replace the public signatures before 1.0; avoid requiring application code to depend on @internal types.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Configuration\PublicSignatureTest::testPublicExtensionSignatureUsesSupportedDto with data set "batch reader return"`
- `App\Tests\Acceptance\Features\Configuration\PublicSignatureTest::testPublicExtensionSignatureUsesSupportedDto with data set "preloader return"`
- `App\Tests\Acceptance\Features\Configuration\PublicSignatureTest::testPublicExtensionSignatureUsesSupportedDto with data set "custom route registry addRoute"`

## MEDIA-DEFAULT-POLICY — P1 / CONFIG_IMPLEMENTATION_GAP

**Ожидается:** Configured default request/response media policies apply consistently to generated reads and writes; explicit negotiation remains authoritative.

**Наблюдается:** Configured application/json request policy is rejected with 415 on generated writes; response defaults stay application/vnd.api+json instead of configured application/json.

**Внешний контракт для бандла:** Resolve request and response media policy uniformly for generated resource routes, including default and negotiated cases.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Protocol\DefaultMediaPolicyTest::testConfiguredDefaultMediaRequestAndResponsePolicy with data set "default"`
- `App\Tests\Acceptance\Features\Protocol\DefaultMediaPolicyTest::testConfiguredDefaultMediaRequestAndResponsePolicy with data set "legacy"`
- `App\Tests\Acceptance\Features\Protocol\DefaultMediaPolicyTest::testConfiguredRequestPolicyAppliesToGeneratedWrites with data set "default"`
- `App\Tests\Acceptance\Features\Protocol\DefaultMediaPolicyTest::testConfiguredRequestPolicyAppliesToGeneratedWrites with data set "legacy"`

## PROFILE-AUDIT-ATTRIBUTE-FIELDS — P1 / DESIRED_CAPABILITY

**Ожидается:** Auditable renamed timestamp/user fields are honored on CREATE and UPDATE.

**Наблюдается:** Renamed insertedBy remains null on CREATE; equivalent configuration mapping passes.

**Внешний контракт для бандла:** Auditable renamed timestamp/user fields are honored on CREATE and UPDATE.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Profiles\AuditFieldNamesTest::testCustomAuditFieldNamesTrackCreateAndUpdate with data set "attribute"`

## RESOURCE-REGISTRY-PROJECTION-COLLISION — P1 / DESIRED_CAPABILITY

**Ожидается:** Registering a DTO projection must not replace the primary entity resource in getByClass.

**Наблюдается:** FeatureArticle class resolves to feature-custom-summaries instead of feature-articles.

**Внешний контракт для бандла:** Registering a DTO projection must not replace the primary entity resource in getByClass.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\RegistryContractTest::testProjectionDoesNotReplacePrimaryEntityClassRegistration`

## RESOURCE-RELATIONSHIP-POLICIES — P1 / DESIRED_CAPABILITY

**Ожидается:** Resource-level relationshipPolicies apply when no per-relationship policy is specified.

**Наблюдается:** Resource map declares VERIFY but effective relationship metadata stays REFERENCE.

**Внешний контракт для бандла:** Resource-level relationshipPolicies apply when no per-relationship policy is specified.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\RegistryContractTest::testResourceLevelRelationshipPolicyAppliesToUnspecifiedRelationship`

## RESOURCE-ROUTE-PREFIX — P1 / DESIRED_CAPABILITY

**Ожидается:** JsonApiResource.routePrefix overrides the global prefix for generated routes and representation links.

**Наблюдается:** GET /reference/routed-articles/1 returns 404 despite resource routePrefix=/reference.

**Внешний контракт для бандла:** Honor the resource-specific route prefix consistently in generated routes and links.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\ResourceOptionsTest::testResourceRoutePrefixOverridesGlobalPrefixAndLinks`

## RESOURCE-TAG-DISCOVERY — P1 / DX_GAP

**Ожидается:** jsonapi.resource service registration composes with directory discovery.

**Наблюдается:** Tagged resource outside discovery paths has no generated route: 404.

**Внешний контракт для бандла:** jsonapi.resource service registration composes with directory discovery.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\ResourceOptionsTest::testExplicitResourceServiceTagWorksOutsideDiscoveryPaths`

## VERSION-RESOLVER-CONTEXT — P1 / DESIRED_CAPABILITY

**Ожидается:** VersionResolver receives negotiated profile and selects the configured DTO mapping.

**Наблюдается:** Negotiated alternate representation returns JSON:API 500 through SHOW, INDEX and related collection; sparse INDEX and included-resource cases pass.

**Внешний контракт для бандла:** Carry request ProfileContext into public representation-definition resolution.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Mapping\PublicInputAndVersionTest::testNegotiatedVersionChangesRepresentation`
- `App\Tests\Acceptance\Features\Mapping\PublicInputAndVersionTest::testVersionSelectionComposesAcrossCollectionsIncludesAndFields with data set "collection"`
- `App\Tests\Acceptance\Features\Mapping\PublicInputAndVersionTest::testVersionSelectionComposesAcrossCollectionsIncludesAndFields with data set "related"`

## CONFIG-HEAD-DISABLED — P2 / CONFIG_IMPLEMENTATION_GAP

**Ожидается:** head_enabled=false disables HEAD and OPTIONS no longer advertises it.

**Наблюдается:** HEAD returns 200 despite explicit false.

**Внешний контракт для бандла:** head_enabled=false disables HEAD and OPTIONS no longer advertises it.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Protocol\DisabledHeadTest::testDisabledHeadIsUnavailableAndOptionsAgrees`

## PROFILE-REL-COUNT-CONFIG — P2 / CONFIG_IMPLEMENTATION_GAP

**Ожидается:** rel_counts.relationship_meta_key names count metadata consistently.

**Наблюдается:** cardinality key absent; default count key still used.

**Внешний контракт для бандла:** rel_counts.relationship_meta_key names count metadata consistently.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Profiles\NegotiationOptionsTest::testCustomRelationshipCountKeyIsUsed`

## PROFILE-REL-COUNT-RELATED-POLICY — P2 / CONFIG_IMPLEMENTATION_GAP

**Ожидается:** compute_in_related_endpoints=false suppresses relationship count computation/exposure on related endpoints.

**Наблюдается:** count is still present when false; true case passes.

**Внешний контракт для бандла:** compute_in_related_endpoints=false suppresses relationship count computation/exposure on related endpoints.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Profiles\RelatedCountOptionsTest::testRelatedEndpointCountPolicy with data set "off"`

## PROFILE-SOFT-DELETE-ACTOR-META — P2 / DOCUMENTATION_GAP

**Ожидается:** Negotiated soft-delete resource metadata exposes an application-supplied deletion actor using SoftDeletable.deletedByField.

**Наблюдается:** Document hook is a documented placeholder; actor metadata is absent.

**Внешний контракт для бандла:** Honor the documented actor metadata mapping or remove the unsupported promise/attribute option before 1.0. Actor assignment remains application policy.

**Падающие проверки:**

- `App\Tests\Acceptance\Features\Profiles\SoftDeleteActorTest::testConfiguredActorFieldAppearsInNegotiatedSoftDeleteMetadata`

## Производительность и решения перед 1.0

`PERFORMANCE-NPLUS1` закрывается только по сохранённым assertions полного прогона. Tenant-safe query-plan bridge передаёт ограничения обоих декораторов; прежний постоянный перерасход не доказывал линейного N+1. Для этого bridge сейчас используется @internal capability/locator, поэтому стабильный публичный контракт всё ещё требует решения владельцев бандла.

Свежие измерения внешнего приложения:

| Сценарий | Запросы: page 5 / page 20 | Бюджет |
|---|---|---|
| testCollectionWithoutIncludeHasBoundedQueryShape | 8 / 8 | 12 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one" | 18 / 18 | 18 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many" | 14 / 14 | 24 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested" | 26 / 26 | 32 |
| testRelatedCollectionQueryCountIsBounded | 9 / 9 | 15 |

Публичные inactive surfaces требуют реализации либо удаления/депрекации до freeze; это отдельные design findings, а не дополнительные HTTP-сбои:

- **TypedResourcePersister**: Retain currently working typed persister API or publish an explicit modern replacement before freeze.
- **TypedRelationshipReader / TypedRelationshipUpdater**: Implement documented typed dispatch or deprecate/remove inactive declarations.
- **RelationshipReadMap in public batch/hook signatures**: Expose a supported public DTO or remove internal types from public signatures.
- **dx.* / errors.locale / inactive Doctrine performance options**: Implement externally verified behavior or remove/deprecate CONFIG_ONLY promises.
- **DoctrineCollectionQueryProviderInterface and ResourceRepositoryLocator::getRepositoryForType**: The tenant-safe query-plan bridge meets measured query budgets, but currently requires an @internal capability and locator method. Publish a stable decorator/provider contract or document that applications must return null and accept the higher-query fallback.

Намеренное ограничение: Atomic поддерживает одну транзакционную границу. Batch через независимые соединения должен отвергаться до первой мутации; распределённая транзакция не требуется.
