# PR #67 independent consumer checklist

The installed Composer revision is recorded in acceptance-results.json. Assertions below execute through Symfony HTTP/kernel routing; vendor files and bundle-owned tests are not evidence. Current case results supersede historical gap descriptions.

| External contract | Consumer evidence | Coverage |
|---|---|---|
| Preconditions checked before mutation; overlapping If-Match has one winner | Production/ConcurrencyAndAtomicTest, Torture/Chaos/ConsistencyAndFailureTest | Existing runnable concurrency assertions |
| Independent PostgreSQL/MySQL and shard transaction boundaries rejected before mutation | Atomic/AtomicTransactionalityTest, Torture/Chaos/ConsistencyAndFailureTest, Torture/Extreme/TenantIsolationTest | 409 and unchanged storage; no distributed transaction expected |
| Same-connection Atomic commits only participating connection | Torture/Chaos/ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection | Explicit COMMIT/database metrics and persisted state |
| Same-connection Atomic failures roll back preceding writes | Atomic/AtomicTransactionalityTest | Validation, unique constraint, unknown resource and relationship failures |
| Generated IDs/lid workflows transactional | Atomic/AtomicLidTest, Production/ConcurrencyAndAtomicTest | Retained HTTP assertions; lid disabling is separately ATOMIC-LID-CONFIG |
| Filter depth/node/operand limits and disabled guards | Features/Filtering/StructuralLimitsTest, DisabledGuardsTest | Boundaries and pre-SQL rejection |
| Weighted relationship path complexity | Features/Filtering/StructuralLimitsTest | Public configuration and HTTP diagnostics |
| Native UUID conversion / malformed UUID client error | Doctrine/UuidIdentifierTest | Existing CRUD, relationship and Atomic assertions |
| Composite identifier discovery rejects at boot | Torture/Extreme/CompositeDiscoveryTest | ARCHITECTURE-COMPOSITE-ID remains a failing contract |
| Distinct root pagination over joined relations | Features/Relationships/RootJoinPaginationTest | Three pages, stable ordering, totals and no duplicate roots |
| DTO order restored after projection | Features/Mapping/ConstructorAndProjectionTest | Different DTO ID property and collection query order |
| Batch representation loading / computed association reader | Features/DataLayer/BatchRelationshipReaderTest, Torture/Performance/NPlusOneAndCardinalityTest | Public reader example works; bounded-query gaps remain independently asserted |
| Relationship identifier budgets | Features/Relationships/IdentifierBudgetTest | Root/include reject; standalone linkage endpoint retains RELATIONSHIP-BUDGET-ENDPOINT |
| Primary resources excluded from included | Existing include tests, Features/Relationships/RepresentationModesTest | Runnable compound-document assertions |
| Explicit include remains connected under linkage=never | Features/Relationships/RepresentationModesTest | Required linkage retained |
| Ambiguous to-many sort rejected; aggregate semantics restore support | Features/Sorting/CollectionPolicyTest, AggregateSemanticsTest | reject policy and application MIN semantics |
| HEAD validators equal GET; OPTIONS reflects available operations | Features/Cache/HeaderConfigurationTest, Features/Mapping/SelectiveOperationsTest | Runtime HTTP green; OpenAPI operation mismatch remains separate |
| Last-Modified configured field / per-type override | Features/Cache/LastModifiedConfigurationTest | Item, collection maximum and conditional response |
| Profile write hooks | Features/Profiles/PublicHooksTest, AuditIdentityTest | Negotiated hook works; per-type default write retains PROFILE-DEFAULT-WRITE |

Paths are relative to tests/Acceptance except explicitly prefixed Torture paths (relative to tests). A working Doctrine transaction boundary does not imply custom-provider Atomic wiring works: DATA-LAYER-CUSTOM-ATOMIC is a separate configuration/integration defect.
