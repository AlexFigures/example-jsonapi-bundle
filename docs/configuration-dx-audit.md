# Configuration and extension audit

The installed Composer dependency is the source of the inventory. These findings distinguish unsupported public promises from ordinary application policy. No application substitute is supplied for an inactive bundle feature.

| Public surface | Installed implementation evidence | Consumer evidence / classification |
|---|---|---|
| `dx.dev_toolbar`, `dx.sandbox`, `dx.doctor`, `dx.maker` | Configuration declarations; no matching runtime integration or commands discovered | CONFIG_ONLY; accepting configuration is not implementation |
| `errors.locale` | Stored as a container parameter, no consumer located | CONFIG_ONLY; no translated error contract verified |
| Doctrine `enable_query_cache`, `query_cache_pool`, `enable_second_level_cache`, `hydrate_partial_by_fields`, `default_fetch` | Names occur in configuration declarations, not provider wiring | CONFIG_ONLY; do not advertise behavior based on defaults |
| `TypedResourcePersister`, `jsonapi.persister` | Legacy public contract/tag; current generated writes select ResourceProcessor instead | DX-TYPED-PERSISTER-DISPATCH; two independently registered types fail through HTTP |
| `TypedRelationshipReader`, `TypedRelationshipUpdater` | Public comments promise tagging, but no active per-type registry/tag binding was located | DOCUMENTATION_ONLY; an application global dispatcher would conceal the missing seam |
| `WriteMapperInterface`, `DefaultWriteMapper` | Public declarations without a discovered write pipeline binding | PARTIAL; operation DTO validation remains WRITE-REQUEST-DTO; no claim of working mapper dispatch |
| `RelationshipBatchReaderInterface` | Active `jsonapi.relationship_batch_reader` registration | BatchRelationshipReaderTest proves computed association serialization/include; public signature returns a DTO marked internal |
| `FetchPlanHookInterface` | Active relationship-count planning | PublicHooksTest proves independently observable counts without enabling the built-in counts profile; its public context also exposes the DTO marked internal |
| `audit_trail.expose_in_meta` | Document hook explicitly describes itself as a placeholder | PROFILE-AUDIT-META, normally executing failing HTTP assertion |
| `last_modified.collections_max_of` | Configuration accepted; resolver always computes maximum | CACHE-COLLECTION-LAST-MODIFIED, normally executing failing HTTP assertion |
| `etag.strategy=version` | Hash generator remains selected | CACHE-VERSION-STRATEGY, normally executing failing HTTP assertions |
| Atomic `enabled` / `endpoint` | Services and public parameters; Symfony route import belongs to the application | Kernel conditionally registers the public AtomicController route using configured parameters; DisabledAtomicTest and EndpointTest verify actual HTTP and OpenAPI |

Inventory statuses are not interchangeable: DOCUMENTATION_ONLY and CONFIG_ONLY are source-audit findings, never green executions. Tests use generated routes; application handlers do not replace missing generic parsing, dispatch or representation behavior. The exhaustive configuration matrix remains visible as PARTIAL where variants are still unverified.

The Torture environment explicitly sets `relationship_max_identifiers: 20000`: its existing high-cardinality contract serializes 10,001 tasks plus other associations. The production default budget legitimately rejected that fixture after cache refresh. This is stress-environment policy, not a bundle defect; normal acceptance keeps dedicated low-budget rejection assertions unchanged.
