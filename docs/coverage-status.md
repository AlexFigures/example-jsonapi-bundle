# Coverage evidence and the completed PARTIAL review

PARTIAL was an evidence status, never approval of incomplete bundle behavior. The former 228 rows were granular configuration leaves, constructor arguments, methods and interfaces; they were not 228 separate runtime defects.

The review now assigns every inventoried row a bounded supported contract, an executable gap, or an explicit inactive/design surface. The final totals come from [feature-coverage.md](feature-coverage.md), regenerated after the full release run. The last 32 exact review decisions and their evidence are reproducible through [feature-review.json](feature-review.json).

| Former uncertainty | Consumer proof / current boundary |
|---|---|
| Application `WriteMapperInterface` + DI | ApplicationWriteMapperTest exercises generated CREATE/UPDATE, persisted transformed values and validation pointers |
| Application resource-meta/filter hooks | PublicHooksTest exercises resource meta and an application-defined filter flag through negotiated HTTP |
| Optional application transaction capability | MemoryArticleProvider implements ResourceWriteTransactionManagerInterface; CustomProviderTest asserts failed Atomic second mutation rolls back the first |
| JSON Schema profile configuration | SchemaProfilesTest checks disabled profile annotations; OpenApiTest checks enabled annotations and local schema refs |
| Custom route options/result forms | HandlerContractTest asserts defaults, requirements, priority, controller/handler forms and response modifiers; RegistryContractTest checks description/registry access |
| Configured input linkage writes | WriteConfigurationTest rejects embedded relationships when disabled and asserts no partial insert; enabled writes retain existing protocol coverage |
| Tenant query-plan capability | Query budgets and tenant isolation pass with explicit safe forwarding; internal capability/locator stability remains a DESIGN_DECISION |

COVERED_GREEN means the linked assertions verify the stated contract. It does not promise every theoretical combination. COVERED_GAP retains a desired failing assertion; current-gaps.json is the authoritative observed-failure inventory. CONFIG_ONLY and DOCUMENTATION_ONLY require bundle implementation/removal decisions before freeze. There are no silent waivers for promised public behavior.

Intentional boundaries remain explicit: cross-connection Atomic rejects before mutation; only a single transaction boundary is supported. NOT is not part of the installed HTTP filter AST and must be rejected with a controlled client error. Assigning a business actor and delivering external side effects remain application responsibilities; advertised metadata mapping and extension interoperability belong to the bundle.
