> Historical iteration report; current evidence is in [release gate](../release-gate.md).

# PR #67 independent verification

Tested bundle: `96a1530f3155ddf001b7d1e48fd33e375c382d85`. Generated from complete HTTP/kernel Acceptance and Torture evidence. No distributed transaction promise; independent connections reject before mutation.

| External contract | Executable evidence | Result |
|---|---|---|
| Preconditions before mutation / overlapping If-Match one winner | Production/ConcurrencyAndAtomicTest, Chaos/ConsistencyAndFailureTest::test | {'PASS': 12} |
| Reject independent Atomic connections before mutation; same-connection rollback | Atomic/AtomicTransactionalityTest, Chaos/ConsistencyAndFailureTest, Extreme/TenantIsolationTest | {'PASS': 20} |
| Generated IDs/lid workflows | Atomic/AtomicLidTest | {'PASS': 5} |
| Filter depth, nodes, operands, weighted path budget and disabled guards | Features/Filtering/StructuralLimitsTest, Features/Filtering/DisabledGuardsTest | {'PASS': 11} |
| Typed UUID / malformed UUID client errors | Doctrine/UuidIdentifierTest | {'PASS': 14} |
| Composite-ID discovery rejected during boot | Extreme/CompositeDiscoveryTest | {'PASS': 1} |
| Distinct root pagination over joins | Features/Relationships/RootJoinPaginationTest | {'PASS': 2} |
| DTO root ordering | Features/Mapping/ConstructorAndProjectionTest | {'PASS': 4} |
| Batch representation reads / structural N+1 budgets | Features/DataLayer/BatchRelationshipReaderTest, Performance/NPlusOneAndCardinalityTest | {'PASS': 7} |
| Relationship identifier budget | Features/Relationships/IdentifierBudgetTest | {'PASS': 3} |
| Primary identity excluded from included / explicit include retains linkage under never | Features/Relationships/RepresentationModesTest | {'PASS': 7} |
| To-many reject policy / custom aggregate sort | Features/Sorting/CollectionPolicyTest, Features/Sorting/AggregateSemanticsTest | {'PASS': 2} |
| HEAD validators / accurate OPTIONS | Protocol/HeadTest, Protocol/OptionsTest, Features/Cache/HeaderConfigurationTest, Features/Mapping/SelectiveOperationsTest | {'PASS': 14} |
| Configured Last-Modified field | Features/Cache/LastModifiedConfigurationTest | {'PASS': 1} |
| Profile default and negotiated write hooks | Features/Profiles/PublicHooksTest, Features/Profiles/AuditIdentityTest | {'PASS': 15} |
| Custom-provider Atomic rollback | Features/DataLayer/CustomProviderTest::testAtomicBusinessFailure | {'PASS': 1} |
