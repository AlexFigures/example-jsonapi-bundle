import json
from collections import Counter
from pathlib import Path
inventory=json.loads(Path('docs/public-feature-inventory.json').read_text())
# Evidence points to independent application tests, never bundle-owned unit tests.
rules=[
('Contract/Tx/ResourceWriteTransactionManagerInterface','Features/DataLayer/TypedProviderTest.php','PARTIAL',''),
('Profile/Hook/ResourceMetaHookInterface','Features/Profiles/AuditIdentityTest.php','PARTIAL',''),
('ServiceTag/jsonapi.persister','Features/DataLayer/TypedProviderTest.php','COVERED_GAP','DX-TYPED-PERSISTER-DISPATCH'),
('jsonapi.media_type=','Features/Protocol/DefaultMediaPolicyTest.php','COVERED_GAP','MEDIA-DEFAULT-POLICY'),
('jsonapi.media_types.default.','Features/Protocol/DefaultMediaPolicyTest.php','COVERED_GAP','MEDIA-DEFAULT-POLICY'),
('jsonapi.media_types.channels.','Features/Protocol/MediaChannelsTest.php','COVERED_GREEN',''),
('Contract/Data/ChangeSet','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Contract/Data/ResourceIdentifier','Features/Relationships/LinkingPoliciesTest.php','COVERED_GREEN',''),
('Contract/Data/SliceIds','Features/Relationships/RootJoinPaginationTest.php','PARTIAL',''),
('Contract/Data/Slice','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Contract/Data/ExistenceChecker','Features/Relationships/LinkingPoliciesTest.php','PARTIAL',''),
('Contract/Data/RepresentationPreloaderInterface','Features/DataLayer/BatchRelationshipReaderTest.php','PARTIAL',''),
('Contract/Data/WriteConcurrencyGuardInterface','Production/ConcurrencyAndAtomicTest.php','PARTIAL',''),
('Contract/Data/ResourcePersister','Features/DataLayer/TypedProviderTest.php','COVERED_GAP','DX-TYPED-PERSISTER-DISPATCH'),
('Contract/Resource/ResourceMetadataInterface','Features/DataLayer/CustomProviderTest.php','PARTIAL',''),
('Contract/Tx/NullTransactionManager','','NOT_APPLICABLE',''),
('Resource/Definition/ReadProjection','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Definition/ResourceDefinition','Features/Mapping/ConstructorAndProjectionTest.php','PARTIAL',''),
('Resource/Definition/VersionDefinition','Features/Mapping/PublicInputAndVersionTest.php','COVERED_GAP','VERSION-RESOLVER-CONTEXT'),
('Resource/Mapper/DefaultReadMapper','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Registry/CustomRouteRegistry','Features/CustomRoutes/HandlerContractTest.php','PARTIAL',''),
('Resource/Registry/ResourceRegistry','Features/DataLayer/CustomProviderTest.php','PARTIAL',''),
('ServiceTag/jsonapi.relationship_batch_reader','Features/DataLayer/BatchRelationshipReaderTest.php','COVERED_GREEN',''),
('jsonapi.relationships.unplanned_read_policy','Features/DataLayer/UnplannedRelationshipTest.php','COVERED_GREEN',''),
('jsonapi.cache.enabled','Features/Cache/DisabledCacheTest.php','COVERED_GREEN',''),
('jsonapi.limits.fields_max_total','Features/Relationships/DocumentBudgetTest.php','COVERED_GREEN',''),
('jsonapi.limits.included_max_resources','Features/Relationships/DocumentBudgetTest.php','COVERED_GREEN',''),
('jsonapi.performance.doctrine.enable_query_cache','','CONFIG_ONLY',''),
('jsonapi.performance.doctrine.query_cache_pool','','CONFIG_ONLY',''),
('jsonapi.performance.doctrine.enable_second_level_cache','','CONFIG_ONLY',''),
('jsonapi.performance.doctrine.hydrate_partial_by_fields','','CONFIG_ONLY',''),
('jsonapi.performance.doctrine.default_fetch','','CONFIG_ONLY',''),
('jsonapi.errors.locale','','CONFIG_ONLY',''),
('jsonapi.errors.','Features/Protocol/ErrorConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.cache.etag.strategy','Features/Cache/VersionStrategyTest.php','COVERED_GAP','CACHE-VERSION-STRATEGY'),
('jsonapi.cache.etag.include_query_shape','Features/Cache/QueryShapeTest.php','COVERED_GREEN',''),
('jsonapi.cache.last_modified.collections_max_of','Features/Cache/DisabledCollectionLastModifiedTest.php','COVERED_GAP','CACHE-COLLECTION-LAST-MODIFIED'),
('jsonapi.cache.last_modified.','Features/Cache/LastModifiedConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.atomic.enabled','Features/Atomic/DisabledAtomicTest.php','COVERED_GREEN',''),
('jsonapi.atomic.endpoint','Features/Atomic/EndpointTest.php','COVERED_GREEN',''),
('jsonapi.profiles.audit_trail.expose_in_meta','Features/Profiles/AuditIdentityTest.php','COVERED_GAP','PROFILE-AUDIT-META'),
('jsonapi.profiles.audit_trail.','Features/Profiles/AuditIdentityTest.php','COVERED_GREEN',''),
('Contract/Data/TypedResourcePersister','Features/DataLayer/TypedProviderTest.php','COVERED_GAP','DX-TYPED-PERSISTER-DISPATCH'),
('Contract/Data/TypedRelationshipReader','','DOCUMENTATION_ONLY',''),
('Contract/Data/TypedRelationshipUpdater','','DOCUMENTATION_ONLY',''),
('Resource/Mapper/WriteMapperInterface','Features/Mapping/PublicInputAndVersionTest.php','PARTIAL','WRITE-REQUEST-DTO'),
('Resource/Mapper/DefaultWriteMapper','Features/Mapping/PublicInputAndVersionTest.php','PARTIAL','WRITE-REQUEST-DTO'),
('Contract/Data/RelationshipBatchReaderInterface','Features/DataLayer/BatchRelationshipReaderTest.php','COVERED_GREEN',''),
('Profile/Hook/FilterParameterProviderInterface','Features/Profiles/SoftDeleteConfigurationTest.php','PARTIAL',''),
('Profile/Hook/FetchPlanHookInterface','Features/Profiles/PublicHooksTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.filter.operator','Features/Filtering/InheritanceAndExtensionsTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.filter.handler','Features/Filtering/HandlerPriorityTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.sort.handler','Features/Sorting/AggregateSemanticsTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.resource_repository','Features/DataLayer/TypedProviderTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.custom_route_handler','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.profile','Features/Profiles/PublicHooksTest.php','COVERED_GREEN',''),
('ServiceTag/jsonapi.resource','Features/Mapping/ResourceOptionsTest.php','COVERED_GAP','RESOURCE-TAG-DISCOVERY'),
('jsonapi.pagination.', 'Query/PaginationTest.php', 'COVERED_GREEN',''),
('jsonapi.strict_content_negotiation','Protocol/ContentNegotiationTest.php','COVERED_GREEN',''),
('jsonapi.route_prefix','Resource/ResourceReadTest.php','COVERED_GREEN',''),
('jsonapi.resource_paths','Features/Configuration/ResourceDiscoveryTest.php','COVERED_GREEN',''),
('jsonapi.data_layer.', 'Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('jsonapi.write.', 'Resource/ResourceCreateTest.php','PARTIAL',''),
('jsonapi.relationships.linkage_in_resource','Features/Relationships/RepresentationModesTest.php','COVERED_GREEN',''),
('jsonapi.relationships.write_response','Features/Relationships/RepresentationModesTest.php','COVERED_GREEN',''),
('jsonapi.limits.filter_', 'Features/Filtering/StructuralLimitsTest.php','COVERED_GREEN',''),
('jsonapi.limits.complexity_budget','Features/Filtering/StructuralLimitsTest.php','COVERED_GREEN',''),
('jsonapi.limits.relationship_max_identifiers','Features/Relationships/IdentifierBudgetTest.php','COVERED_GAP','RELATIONSHIP-BUDGET-ENDPOINT'),
('jsonapi.limits.include_', 'Features/Relationships/IncludeLimitsTest.php','COVERED_GREEN',''),
('jsonapi.limits.page_max_size','Query/PaginationTest.php','COVERED_GREEN',''),
('jsonapi.performance.doctrine.collection_sort_policy','Features/Sorting/AggregateSemanticsTest.php','COVERED_GREEN',''),
('jsonapi.performance.head_enabled','Features/Protocol/DisabledHeadTest.php','COVERED_GAP','CONFIG-HEAD-DISABLED'),
('jsonapi.cache.etag.hash_algo','Features/Cache/HeaderConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.cache.etag.weak_for_collections','Features/Cache/HeaderConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.cache.etag.', 'Cache/ConditionalRequestsTest.php','PARTIAL',''),
('jsonapi.cache.headers.', 'Features/Cache/HeaderConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.cache.vary.', 'Features/Cache/HeaderConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.cache.surrogate_keys.', 'Features/Cache/HeaderConfigurationTest.php','COVERED_GAP','CACHE-SURROGATE-ROUTES'),
('jsonapi.cache.conditional.', 'Features/Cache/ConditionalSwitchesTest.php','COVERED_GREEN',''),
('jsonapi.cache.last_modified.', 'Cache/ConditionalRequestsTest.php','PARTIAL',''),
('jsonapi.atomic.return_policy','Features/Atomic/ConfigurationMatrixTest.php','COVERED_GREEN',''),
('jsonapi.atomic.require_ext_header','Features/Atomic/ConfigurationMatrixTest.php','COVERED_GREEN',''),
('jsonapi.atomic.max_operations','Features/Atomic/ConfigurationMatrixTest.php','COVERED_GREEN',''),
('jsonapi.atomic.allow_href','Features/Atomic/ConfigurationMatrixTest.php','COVERED_GREEN',''),
('jsonapi.atomic.lid.', 'Features/Atomic/ConfigurationMatrixTest.php','COVERED_GAP','ATOMIC-LID-CONFIG'),
('jsonapi.atomic.', 'Atomic/AtomicTransactionalityTest.php','PARTIAL',''),
('jsonapi.docs.generator.json_schema.include_profiles','Features/Docs/SchemaProfilesTest.php','COVERED_GREEN',''),
('jsonapi.docs.generator.json_schema.', 'Features/Docs/OpenApiTest.php','COVERED_GAP','CONFIG-JSON-SCHEMA'),
('jsonapi.docs.generator.openapi.', 'Features/Docs/OpenApiTest.php','COVERED_GREEN',''),
('jsonapi.docs.ui.', 'Features/Docs/RedocTest.php','COVERED_GREEN',''),
('jsonapi.dx.', '', 'CONFIG_ONLY',''),
('jsonapi.release.', '', 'CONFIG_ONLY',''),
('jsonapi.profiles.soft_delete.strategy','Features/Profiles/BooleanSoftDeleteTest.php','COVERED_GAP','PROFILE-SOFT-BOOLEAN'),
('jsonapi.profiles.soft_delete.default_visibility','Features/Profiles/SoftDeleteConfigurationTest.php','COVERED_GAP','PROFILE-SOFT-VISIBILITY'),
('jsonapi.profiles.soft_delete.delete_semantics','Features/Profiles/SoftDeleteConfigurationTest.php','COVERED_GAP','PROFILE-SOFT-DELETE-SEMANTICS'),
('jsonapi.profiles.soft_delete.query_flags.with_deleted','Features/Profiles/SoftDeleteConfigurationTest.php','COVERED_GAP','PROFILE-SOFT-QUERY-FLAGS'),
('jsonapi.profiles.soft_delete.', 'Features/Profiles/SoftDeleteConfigurationTest.php','COVERED_GREEN',''),
('jsonapi.profiles.rel_counts.relationship_meta_key','Features/Profiles/NegotiationOptionsTest.php','COVERED_GAP','PROFILE-REL-COUNT-CONFIG'),
('jsonapi.profiles.rel_counts.compute_in_related_endpoints','Features/Profiles/RelatedCountOptionsTest.php','COVERED_GAP','PROFILE-REL-COUNT-RELATED-POLICY'),
('jsonapi.profiles.negotiation.','Features/Profiles/NegotiationOptionsTest.php','COVERED_GREEN',''),
('jsonapi.profiles.enabled_by_default.','Features/Profiles/NegotiationOptionsTest.php','COVERED_GREEN',''),
('jsonapi.profiles.per_type.','Features/Profiles/NegotiationOptionsTest.php','COVERED_GREEN',''),
('jsonapi.profiles.', 'Profiles/ProfileTest.php','PARTIAL',''),
('Bridge/Symfony/Routing/Attribute/MediaChannel','Features/Protocol/MediaChannelsTest.php','COVERED_GAP','MEDIA-CHANNEL-ROUTING'),
('jsonapi.media_types.', 'Atomic/AtomicNegotiationTest.php','PARTIAL',''),
('jsonapi.errors.', 'Protocol/ErrorDocumentTest.php','PARTIAL',''),
('Resource/Attribute/JsonApiResource::$writeRequests','Features/Mapping/PublicInputAndVersionTest.php','COVERED_GAP','WRITE-REQUEST-DTO'),
('Resource/Attribute/JsonApiResource::$versionResolver','Features/Mapping/PublicInputAndVersionTest.php','COVERED_GAP','VERSION-RESOLVER-CONTEXT'),
('Resource/Attribute/JsonApiResource::$fieldMap','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Attribute/JsonApiResource::$operations','Features/Mapping/SelectiveOperationsTest.php','COVERED_GREEN',''),
('Resource/Attribute/JsonApiResource::$routePrefix','Features/Mapping/ResourceOptionsTest.php','COVERED_GAP','RESOURCE-ROUTE-PREFIX'),
('Resource/Attribute/JsonApiResource::$exposeId','Features/Mapping/ResourceOptionsTest.php','COVERED_GAP','DOCS-EXPOSE-ID-CONTRACT'),
('Resource/Attribute/JsonApiResource::$relationshipPolicies','Features/Mapping/RegistryContractTest.php','COVERED_GAP','RESOURCE-RELATIONSHIP-POLICIES'),
('Resource/Attribute/JsonApiResource::$viewClass','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Attribute/JsonApiResource::$readProjection','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Attribute/FilterableField','Features/Filtering/InheritanceAndExtensionsTest.php','COVERED_GREEN',''),
('Resource/Attribute/SortableField','Features/Sorting/AggregateSemanticsTest.php','COVERED_GREEN',''),
('Resource/Attribute/Relationship','Features/Relationships/PathAliasTest.php','COVERED_GREEN',''),
('Resource/Attribute/JsonApiCustomRoute::$description','Features/CustomRoutes/HandlerContractTest.php','PARTIAL',''),
('Resource/Attribute/JsonApiCustomRoute','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('Resource/Attribute/Attribute','Mapping/FieldAliasesTest.php','COVERED_GREEN',''),
('Resource/Attribute/Id','Mapping/FieldAliasesTest.php','COVERED_GREEN',''),
('Resource/Attribute/', 'Mapping/FieldAliasesTest.php','PARTIAL',''),
('Docs/Attribute/', 'Features/Docs/OpenApiTest.php','COVERED_GREEN',''),
('Contract/Data/TypedResourceRepository','Features/DataLayer/TypedProviderTest.php','COVERED_GREEN',''),
('Contract/Data/ResourceRepository','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Contract/Data/ResourceProcessor','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Contract/Data/RelationshipReader','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Contract/Data/RelationshipUpdater','Production/RelationshipPolicyTest.php','COVERED_GREEN',''),
('Contract/Tx/ScopedTransactionManagerInterface','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('Contract/Tx/TransactionManager','Features/DataLayer/CustomProviderTest.php','COVERED_GREEN',''),
('Profile/Hook/ReadHook','Features/Profiles/PublicHooksTest.php','COVERED_GAP','PROFILE-READ-HOOK'),
('Profile/Hook/RelationshipHook','Features/Profiles/PublicHooksTest.php','COVERED_GAP','PROFILE-RELATIONSHIP-HOOK'),
('Profile/Hook/FetchPlanHookInterface','Features/Profiles/PublicHooksTest.php','PARTIAL',''),
('Profile/Hook/','Features/Profiles/PublicHooksTest.php','COVERED_GREEN',''),
('Profile/Attribute/','Profiles/ProfileTest.php','PARTIAL',''),
('Filter/Operator/','Query/FilteringTest.php','COVERED_GREEN',''),
('Filter/Handler/FilterHandlerInterface','Features/Filtering/SearchCompositionTest.php','COVERED_GREEN',''),
('Filter/Handler/SortHandlerInterface','Features/Sorting/AggregateSemanticsTest.php','COVERED_GREEN',''),
('CustomRoute/Attribute/NoTransaction','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('CustomRoute/Handler/','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('CustomRoute/Result/CustomRouteResult','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('CustomRoute/Query/CriteriaBuilder','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('CustomRoute/Context/','Features/CustomRoutes/HandlerContractTest.php','COVERED_GREEN',''),
('Resource/Definition/VersionResolverInterface','Features/Mapping/PublicInputAndVersionTest.php','COVERED_GAP','VERSION-RESOLVER-CONTEXT'),
('Resource/Definition/ReadProjection::CUSTOM','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Mapper/ReadMapperInterface','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Definition/ReadProjection::DTO','Features/Mapping/ConstructorAndProjectionTest.php','COVERED_GREEN',''),
('Resource/Definition/ReadProjection::ENTITY','Resource/ResourceReadTest.php','COVERED_GREEN',''),
('Resource/Definition/ResourceOperation','Features/Mapping/SelectiveOperationsTest.php','COVERED_GREEN',''),
('Resource/Metadata/RelationshipLinkingPolicy','Features/Relationships/LinkingPoliciesTest.php','COVERED_GREEN',''),
('Events/','Features/Events/ResourceEventsTest.php','COVERED_GREEN',''),
('Http/Response/JsonApiResponseFactory','Features/CustomRoutes/ResponseFactoryTest.php','COVERED_GREEN',''),
('Http/Response/JsonApiResponseBuilder','Features/CustomRoutes/ResponseFactoryTest.php','COVERED_GREEN',''),
 ('Profile/ProfileInterface','Production/ExtensibilityTest.php','COVERED_GREEN',''),
]
report_path=Path('docs/acceptance-results.json')
report=json.loads(report_path.read_text()) if report_path.exists() else {}
gap_states={g['id']:g['status'] for g in report.get('gap_summary', [])} if report.get('bundle_revision')==inventory['bundle_revision'] else {}
rows=[]
reviewed=json.loads(Path('docs/feature-review.json').read_text())['features']
for item in inventory['features']:
 name=item['feature']; test='';status='NOT_COVERED';gap=''
 for prefix,test0,status0,gap0 in rules:
  if (name == prefix[:-1] if prefix.endswith('=') else name.startswith(prefix)):test,status,gap=test0,status0,gap0;break
 if name in reviewed:
  review=reviewed[name]
  test,status,gap=review['test'],review['status'],review['gap']
 if gap and gap_states.get(gap)=='RESOLVED_ON_TESTED_REVISION' and status=='COVERED_GAP': status='COVERED_GREEN'
 if status=='NOT_APPLICABLE': finding='Value/default implementation without a separate application-facing HTTP operation.'
 elif status=='DOCUMENTATION_ONLY': finding='Public legacy interface claims typed dispatch but has no discovered active registration path; see configuration-dx-audit.md.'
 elif status=='NOT_COVERED': finding='No independent execution evidence yet.'
 elif status=='CONFIG_ONLY': finding='Source audit: schema/parameter storage exists, no corresponding feature implementation located.'
 elif gap and gap_states.get(gap)=='RESOLVED_ON_TESTED_REVISION': finding='Historical gap resolved; the bounded consumer contract remains a regression assertion.'
 else: finding='See current-gaps.json for observed failures.' if gap else 'The linked independent consumer assertions define the verified contract; no claim of every combinatorial permutation.'
 if name in reviewed: finding=reviewed[name]['contract']
 rows.append(dict(feature=name,public_api=item['public_api'],status=status,test=test,gap=gap,gap_status=gap_states.get(gap) if gap else None,finding=finding))
counts=Counter(r['status'] for r in rows)
lines=['# Public feature coverage','',f"Installed bundle: `{inventory['bundle_revision']}`. Inventory generated by `php tools/public-feature-inventory.php` from the public configuration tree, attributes, contracts, enums and extension/result methods. No bundle-owned test counts as evidence.",'',f"Explicit inventory: **{len(rows)} entries**. "+', '.join(f'{k}: {v}' for k,v in sorted(counts.items()))+'. No percentage: constructor options, configuration leaves and feature contracts are deliberately separate entries.','', 'COVERED_GREEN means the referenced consumer assertions exercise the recorded contract. PARTIAL means variants or composition remain unverified. CONFIG_ONLY records a source-audited surface without implemented runtime tooling; it is not a passing feature. HTTP results and open gaps are in [acceptance-status.md](acceptance-status.md).','', '## Public surface inventory','', '| Feature | Public API/configuration | Example scenario | Existing coverage / new test | Status | Gap ID | DX/documentation finding |','|---|---|---|---|---|---|---|']
for r in rows:
 evidence=('[test](../tests/Acceptance/'+r['test']+')' if r['test'] else '—')
 lines.append('| '+' | '.join([r['feature'],r['public_api'],r['test'].split('/')[0] if r['test'] else ('Source audit' if r['status'] in ['CONFIG_ONLY','DOCUMENTATION_ONLY','NOT_APPLICABLE'] else 'Pending'),evidence,r['status'],r['gap'] or '—',r['finding']])+' |')
lines+=['', '## Reviewed contract boundaries', '', 'The former 228 PARTIAL entries have been reviewed against explicit consumer assertions. Exact residual decisions and their semantic bounds are recorded in [feature-review.json](feature-review.json); the generator applies these decisions reproducibly. A green row proves the linked bounded contract, not all theoretical permutations. Runtime/DX failures remain COVERED_GAP; inactive surfaces remain CONFIG_ONLY or DOCUMENTATION_ONLY and require implementation or removal before the public API freeze.', '', 'Cross-connection Atomic is intentionally unsupported and must reject before mutation. NOT is not a supported HTTP filter AST; its controlled rejection is a regression test. Side-effect delivery and domain-specific actor assignment remain application policy. Tenant-safe query-plan forwarding meets the retained Torture budgets, but the currently internal capability/locator contract requires a bundle API design decision.', '']
Path('docs/feature-coverage.md').write_text('\n'.join(lines).rstrip()+'\n')
Path('docs/feature-coverage.json').write_text(json.dumps(dict(bundle_revision=inventory['bundle_revision'],counts=dict(counts),features=rows),indent=2)+'\n')
print(dict(counts))
audit_path=Path('docs/partial-review.json')
if audit_path.exists():
 audit=json.loads(audit_path.read_text())
 by_name={r['feature']:r for r in rows}
 audit['bundle_revision']=inventory['bundle_revision']
 audit['features']=[by_name[name] for name in audit['baseline_features']]
 audit['outcomes']=dict(Counter(r['status'] for r in audit['features']))
 audit_path.write_text(json.dumps(audit,indent=2)+'\n')
 review_lines=['# Completed review of 228 PARTIAL entries','', 'Tested revision: `'+inventory['bundle_revision']+'`.', '', 'Each original row now has evidence or an executable gap. Results: '+str(audit['outcomes'])+'. Green proves the linked bounded assertion contract, not every theoretical permutation.', '', '| Original feature | Reviewed status | Consumer evidence | Gap |','|---|---|---|---|']
 for r in audit['features']:
  review_lines.append('| '+r['feature']+' | '+r['status']+' | [test](../tests/Acceptance/'+r['test']+') | '+(r['gap'] or '—')+' |')
 review_lines+=['', 'The 32 final exact semantic decisions are recorded in [feature-review.json](feature-review.json). Current observed failures are in [current-gaps.json](current-gaps.json); inactive API/configuration surfaces require explicit bundle implementation/removal decisions before freeze.', '']
 Path('docs/partial-review.md').write_text('\n'.join(review_lines))
