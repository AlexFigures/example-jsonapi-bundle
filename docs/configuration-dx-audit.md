# Configuration and extension audit

The installed Composer dependency is the source of the inventory. These findings distinguish unsupported public promises from ordinary application policy. No application substitute is supplied for an inactive bundle feature.

| Public surface | Installed implementation evidence | Consumer evidence / classification |
|---|---|---|
| `dx.dev_toolbar`, `dx.sandbox`, `dx.doctor`, `dx.maker` | Configuration declarations; no matching runtime integration or commands discovered | CONFIG_ONLY; accepting configuration is not implementation |
| `errors.locale` | Stored as a container parameter, no consumer located | CONFIG_ONLY; no translated error contract verified |
| Doctrine `enable_query_cache`, `query_cache_pool`, `enable_second_level_cache`, `hydrate_partial_by_fields`, `default_fetch` | Names occur in configuration declarations, not provider wiring | CONFIG_ONLY; do not advertise behavior based on defaults |
| `TypedResourcePersister`, `jsonapi.persister` | Latest revision documents and wires typed persister dispatch | Two independently registered types are retained as HTTP regression assertions; see current release results |
| `TypedRelationshipReader`, `TypedRelationshipUpdater` | Public comments promise tagging, but no active per-type registry/tag binding was located | DOCUMENTATION_ONLY; an application global dispatcher would conceal the missing seam |
| `WriteMapperInterface`, `DefaultWriteMapper` | Active default mapper alias and operation-input pipeline binding | CREATE/UPDATE/Atomic consumer contract; custom mapper overrides remain a design/evidence question |
| `RelationshipBatchReaderInterface` | Active `jsonapi.relationship_batch_reader` registration | BatchRelationshipReaderTest proves computed association serialization/include; public signature returns a DTO marked internal |
| `FetchPlanHookInterface` | Active relationship-count planning | PublicHooksTest proves independently observable counts without enabling the built-in counts profile; its public context also exposes the DTO marked internal |
| `audit_trail.expose_in_meta` | Audit document hook present; negotiated/server-default activation exercised | PROFILE-AUDIT-META historical contract; current results determine open/resolved |
| `last_modified.collections_max_of` | Boolean option retained with both enabled/disabled external assertions | Current external results determine whether collection header suppression is honored |
| `etag.strategy=version` | Version/header and hash strategies have independent external assertions | Current external results determine whether version strategy is honored |
| Atomic `enabled` / `endpoint` | Services and public parameters; Symfony route import belongs to the application | Kernel conditionally registers the public AtomicController route using configured parameters; DisabledAtomicTest and EndpointTest verify actual HTTP and OpenAPI |

Inventory statuses are not interchangeable: DOCUMENTATION_ONLY and CONFIG_ONLY are source-audit findings, never green executions. Tests use generated routes; application handlers do not replace missing generic parsing, dispatch or representation behavior. The exhaustive configuration matrix remains visible as PARTIAL where variants are still unverified.

The Torture environment explicitly sets `relationship_max_identifiers: 20000`: its existing high-cardinality contract serializes 10,001 tasks plus other associations. The production default budget legitimately rejected that fixture after cache refresh. This is stress-environment policy, not a bundle defect; normal acceptance keeps dedicated low-budget rejection assertions unchanged.

This source audit was refreshed against `e0b6f935fe5bbc1ae577a4ef2a6c414eb393ca6a`. Historical IDs above are not an open-gap list; [current-gaps.json](current-gaps.json) is authoritative.

The updated revision introduces ResourceMetaHookInterface and ResourceWriteTransactionManagerInterface. PublicHooksTest now exercises an application-owned resource-meta hook through HTTP; MemoryArticleProvider implements the transaction capability and CustomProviderTest verifies generated writes and Atomic rollback. Their bounded consumer contracts are recorded in feature-review.json; they are no longer PARTIAL.
