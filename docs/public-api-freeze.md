# Public API freeze review

This is a consumer recommendation, not a bundle architecture decision. The owner must confirm the stable contract or remove/internalize unsupported declarations before 1.0. Executable outcomes are in [release-gate.md](release-gate.md); every public declaration has an evidence status in [feature-coverage.md](feature-coverage.md).

| Surface | Proposed freeze group | External evidence / decision |
|---|---|---|
| Resource attributes, generated operations, filters/operators/sorts, custom routes, ResponseFactory | STABILIZE_FOR_1_0 | HTTP examples and fixed composition journeys; only the bounded verified contracts recorded by the feature inventory |
| `writeRequests`, `WriteMapperInterface` | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | CREATE/UPDATE independent constraints, partial PATCH, unknown/server-owned input, relationship and Atomic validation in PublicInputAndVersionTest; current default mapper wiring is active |
| `VersionResolverInterface` | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | One negotiated alternate view through SHOW, INDEX, sparse, include, related; stable identities. Representation selection is unrelated to cache/version ETags |
| `TypedResourcePersister` / `jsonapi.persister` | STABILIZE_FOR_1_0 | Two-type HTTP dispatch regression; latest documentation explicitly supports this legacy name. Current @api declaration and public extension index retain the existing persister API and type dispatch adapter |
| `TypedRelationshipReader`, `TypedRelationshipUpdater` | DESIGN_DECISION → DEPRECATE_OR_REMOVE_BEFORE_1_0 or implement | Latest revision documents active typed dispatch/tags; dedicated consumer two-type relationship dispatch verification remains needed |
| `RelationshipBatchReaderInterface` | STABILIZE_FOR_1_0 | Computed association HTTP/include, bounded batch queries and budget; required `RelationshipReadMap` is now explicitly public @api |
| `RelationshipReadMap`, `CustomRouteMetadata` | STABILIZE_FOR_1_0 | Explicit @api DTOs; public batch/preloader/registry signature assertions retain this contract |
| Document/Query/Read/Write/Relationship/FetchPlan profile hooks | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | Visible HTTP effects, DI, default/negotiated activation; metadata/default-write outcomes remain executable |
| `dx.dev_toolbar`, `dx.sandbox.*`, `dx.doctor.*`, `dx.maker.*` | DEPRECATED / not stable functionality | CONFIG_ONLY; explicitly deprecated as unimplemented |
| `errors.locale` | DEPRECATED / not stable functionality | Explicitly deprecated as unimplemented |
| Doctrine query/second-level cache, partial hydration, default-fetch options | DEPRECATED / not stable functionality | All five inactive options explicitly deprecated; see configuration audit |
| `ResourceMetaHookInterface`, `ResourceWriteTransactionManagerInterface` | DESIGN_DECISION → STABILIZE_FOR_1_0 with bounded semantics | New public interfaces on the updated revision; built-in audit metadata and typed generated writes provide integration evidence. Application implementations now have HTTP evidence in PublicHooksTest and CustomProviderTest |
| Controller internals, compiler passes, bridge implementation helpers | INTERNALIZE | The example does not instantiate internal controllers or promise their behavior as extension APIs |
| Cross-connection Atomic | DESIGN_DECISION confirmed: reject before mutation | No distributed transaction promise. Same-connection batches retain all-or-nothing semantics |

CONFIG_ONLY and DOCUMENTATION_ONLY are freeze questions, not executable runtime bugs. The former PARTIAL entries now have bounded executable contracts or explicit gaps; the review manifest records the evidence. Do not treat a green aggregate test count as proof of every theoretical permutation.

Logical custom predicates are frozen for AND/OR and nested branches. The installed HTTP parser exposes only AND/OR; NOT is not a supported AST input. NativeOperatorsTest retains its controlled-400 assertion, so no unsupported NOT semantics are invented.

Tenant-safe query-plan forwarding meets all retained page 5/20 query budgets. On `a17ffd40a7d3a1e642a33aaf788427bb6b117fdb`, `DoctrineCollectionQueryProviderInterface`, `ResourceRepositoryLocator` and `getRepositoryForType` are explicitly marked `@api`. The focused decorator capability decision is resolved; PublicSignatureTest protects the annotations and Torture protects the visibility/query behavior. General bundle API freeze review remains separate.
