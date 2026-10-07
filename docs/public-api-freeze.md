# Public API freeze review

This is a consumer recommendation, not a bundle architecture decision. The owner must confirm the stable contract or remove/internalize unsupported declarations before 1.0. Executable outcomes are in [release-gate.md](release-gate.md); every public declaration has an evidence status in [feature-coverage.md](feature-coverage.md).

| Surface | Proposed freeze group | External evidence / decision |
|---|---|---|
| Resource attributes, generated operations, filters/operators/sorts, custom routes, ResponseFactory | STABILIZE_FOR_1_0 | HTTP examples and fixed composition journeys; only the bounded verified contracts recorded by the feature inventory |
| `writeRequests`, `WriteMapperInterface` | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | CREATE/UPDATE independent constraints, partial PATCH, unknown/server-owned input, relationship and Atomic validation in PublicInputAndVersionTest; current default mapper wiring is active |
| `VersionResolverInterface` | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | One negotiated alternate view through SHOW, INDEX, sparse, include, related; stable identities. Representation selection is unrelated to cache/version ETags |
| `TypedResourcePersister` / `jsonapi.persister` | DESIGN_DECISION → STABILIZE_FOR_1_0 if retained | Two-type HTTP dispatch regression; latest documentation explicitly supports this legacy name. Decide modern naming/deprecation before freezing, without an application dispatcher |
| `TypedRelationshipReader`, `TypedRelationshipUpdater` | DESIGN_DECISION → DEPRECATE_OR_REMOVE_BEFORE_1_0 or implement | Public comments promise typed tagging; no corresponding active registry binding located. No claim of verified runtime behavior |
| `RelationshipBatchReaderInterface` | STABILIZE_FOR_1_0 | Computed association HTTP/include, bounded batch queries and budget; `RelationshipReadMap` is marked internal while exposed by the public signature |
| `RelationshipReadMap` and profile hook context DTOs | DESIGN_DECISION → INTERNALIZE or expose supported DTO | Public extension signatures must not depend on semantic contracts consumers are told are internal |
| Document/Query/Read/Write/Relationship/FetchPlan profile hooks | STABILIZE_FOR_1_0 or KEEP_BUT_FIX according to current results | Visible HTTP effects, DI, default/negotiated activation; metadata/default-write outcomes remain executable |
| `dx.dev_toolbar`, `dx.sandbox.*`, `dx.doctor.*`, `dx.maker.*` | DESIGN_DECISION → DEPRECATE_OR_REMOVE_BEFORE_1_0 | CONFIG_ONLY; no fake application implementation |
| `errors.locale` | DESIGN_DECISION → DEPRECATE_OR_REMOVE_BEFORE_1_0 | Parameter stored; no translation consumer located |
| Doctrine query/second-level cache, partial hydration, default-fetch options | DESIGN_DECISION → DEPRECATE_OR_REMOVE_BEFORE_1_0 | CONFIG_ONLY; see configuration audit for exact keys |
| `ResourceMetaHookInterface`, `ResourceWriteTransactionManagerInterface` | DESIGN_DECISION → STABILIZE_FOR_1_0 with bounded semantics | New public interfaces on the updated revision; built-in audit metadata and typed generated writes provide integration evidence. Application implementations now have HTTP evidence in PublicHooksTest and CustomProviderTest |
| Controller internals, compiler passes, bridge implementation helpers | INTERNALIZE | The example does not instantiate internal controllers or promise their behavior as extension APIs |
| Cross-connection Atomic | DESIGN_DECISION confirmed: reject before mutation | No distributed transaction promise. Same-connection batches retain all-or-nothing semantics |

CONFIG_ONLY and DOCUMENTATION_ONLY are freeze questions, not executable runtime bugs. The former PARTIAL entries now have bounded executable contracts or explicit gaps; the review manifest records the evidence. Do not treat a green aggregate test count as proof of every theoretical permutation.

Logical custom predicates are frozen for AND/OR and nested branches. The installed HTTP parser exposes only AND/OR; NOT is not a supported AST input. NativeOperatorsTest retains its controlled-400 assertion, so no unsupported NOT semantics are invented.

Tenant-safe query-plan forwarding now meets all retained page 5/20 query budgets. `DoctrineCollectionQueryProviderInterface` is still marked `@internal`, and forwarding through the bundle locator calls `getRepositoryForType`. Before freeze, publish a supported capability/dispatch contract or explicitly require safe fallback. The runtime performance gap and this API design decision are separate findings.
