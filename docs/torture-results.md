# Production torture results

Generated from complete JUnit and scenario HTTP metrics on bundle `8750b80831dba484f3de0ff55c345fec5fdc29e0`.

- HTTP test outcome PASS: 62
- PASS: 57
- BUNDLE_GAP: 0
- APPLICATION_POLICY: 4
- INFRASTRUCTURE_LIMIT: 1
- UNEXPECTED_FAILURE: 0
- SKIPPED: 0

Wall times are observations, not CI thresholds. Memory is the PHP process peak since request start; baseline includes loaded classes. Rows fetched measures DBAL fetches, not exact ORM hydration. Routing records actual physical database names. Dataset counts exclude the extra BIGINT row.

## Scenario matrix

| Scenario | Result | HTTP | SQL counts | Wall ms | Peak MiB | Response bytes | Dataset tasks |
|---|---|---|---|---|---|---|---|
| [Extreme\CompositeDiscoveryTest::testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery](../tests/Torture/Extreme/CompositeDiscoveryTest.php) | PASS / PASS |  |  |  |  |  |  |
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 12 | 93.591 | 52.5 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 4, 7, 5 | 23.084, 68.006, 38.31 | 52.5, 52.5, 52.5 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 24 | 55.845 | 52.5 | 7125 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 400 | 0 | 4.125 | 52.5 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 9, 3, 2, 7, 4, 1 | 14.197, 47.127, 46.841, 32.566, 40.102, 36.506, 29.684, 21.363 | 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 52.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 7, 10, 7 | 20.308, 78.791, 40.972 | 52.5, 52.5, 52.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 9, 1, 1 | 26.854, 27.176, 30.883 | 52.5, 52.5, 52.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200 | 13, 13, 13 | 48.387, 84.178, 84.708 | 52.5, 52.5, 52.5 | 30203, 30391, 30166 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200, 200, 200, 200, 200 | 8, 8, 8, 10, 8, 8, 8 | 23.666, 62.13, 54.421, 62.947, 53.916, 56.076, 57.134 | 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 52.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 4.594 | 52.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 6.251 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 6.059 | 54.5 | 225 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 2.456 | 54.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 4.011 | 54.5 | 227 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 200 | 9 | 32.824 | 54.5 | 8147 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 27.117, 49.388, 58.803, 49.861, 55.993, 32.182, 38.694, 36.976 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 32.234, 54.78, 51.36, 27.37, 42.995, 35.284, 39.948, 41.282 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 26.072, 23.557, 15.443 | 54.5, 54.5, 54.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 404, 404, 200 | 3, 1, 2, 3 | 10.825, 31.578, 29.047, 29.434 | 54.5, 54.5, 54.5, 54.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200 | 0, 3 | 1.891, 34.702 | 54.5, 54.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200, 200 | 0, 5, 5 | 2.088, 42.96, 52.912 | 54.5, 54.5, 54.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 5, 3, 3, 9, 5 | 50.836, 46.172, 33.827, 43.753, 67.636 | 60.5, 62.5, 60.5, 60.5, 66.5 | 290767, 2887, 2775, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200 | 5, 1 | 44.884, 75.035 | 68.5, 68.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 400 | 9 | 27.957 | 68.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS / PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 32.715, 78.443, 44.037, 76.802 | 68.5, 68.5, 68.5, 68.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 8, 8 | 26.681, 61.776 | 68.5, 68.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 18, 18 | 43.786, 79.658 | 68.5, 68.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 14, 14 | 33.904, 97.676 | 68.5, 68.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 26, 26 | 73.768, 117.83 | 68.5, 68.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 9, 9 | 31.591, 69.969 | 68.5, 68.5 | 8194, 30889 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200 | 2 | 6.266 | 68.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 15, 6, 15, 10, 15 | 30.525, 53.616, 57.956, 22.66, 67.98 | 68.5, 68.5, 68.5, 68.5, 68.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 304, 200 | 7, 10, 7, 7 | 22.424, 17.869, 11.505, 8.53 | 68.5, 68.5, 68.5, 68.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 22.002, 15.937, 14.278 | 68.5, 68.5, 68.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / INFRASTRUCTURE_LIMIT | 200 | 5 | 47.532 | 68.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201 | 8 | 42.795 | 68.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 412, 200 | 7, 16, 7, 7 | 19.537, 165.754, 209.488, 30.749 | 68.5, 22, 22, 68.5 | 1553, 1557, 239, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 201, 200 | 3, 7, 6 | 123.861, 161.355, 8.652 | 18, 22, 68.5 | 191, 1238, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 200 | 0, 5 | 4.89, 47.412 | 68.5, 68.5 | 336, 2536 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 500 | 0 | 5.065 | 68.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200 | 6, 5 | 12.015, 47.95 | 68.5, 68.5 | 2464, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.463, 57.539 | 68.5, 68.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 3.772, 50.541 | 68.5, 68.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.76, 54.894 | 68.5, 68.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200 | 10 | 98.76 | 89.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400 | 0 | 9.68 | 68.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / APPLICATION_POLICY | 413, 413 | 0, 0 | 2.144, 3.547 | 70.5, 70.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200, 400, 200 | 6, 0, 5 | 28.343, 2.163, 44.713 | 68.5, 68.5, 68.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200, 200 | 5, 8, 5, 5 | 42.55, 47.755, 32.532, 32.324 | 68.5, 68.5, 68.5, 68.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200 | 9, 3, 3 | 43.852, 39.53, 30.797 | 68.5, 68.5, 68.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200 | 6, 5 | 33.771, 50.963 | 68.5, 68.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 201, 404, 204, 404 | 8, 1, 4, 1 | 45.042, 33.353, 32.296, 23.51 | 68.5, 68.5, 68.5, 68.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / APPLICATION_POLICY | 200, 200, 200 | 8, 5, 5 | 48.574, 9.32, 45.065 | 68.5, 68.5, 68.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 406, 200, 200, 200 | 5, 0, 7, 24, 7 | 18.151, 0.764, 15.734, 39.279, 11.792 | 68.5, 68.5, 68.5, 68.5, 68.5 | 601, 245, 1553, 7125, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 400, 200 | 3, 0, 3 | 5.119, 0.982, 2.842 | 68.5, 68.5, 68.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 11.507, 48.565, 38.872 | 68.5, 68.5, 68.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 16.364, 46.076, 38.46 | 68.5, 68.5, 68.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 10.149, 42.672, 37.958 | 68.5, 68.5, 68.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200 | 4, 7 | 104.275, 47.338 | 68.5, 68.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 409, 200, 200 | 0, 5, 1 | 2.749, 58.754, 33.161 | 68.5, 68.5, 68.5 | 336, 2536, 243 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 200, 200, 200, 200 | 10, 10, 10, 3 | 15.464, 118.673, 115.809, 24.233 | 68.5, 18, 18, 68.5 | 139, 189, 189, 243 | 100, 100, 100, 100 |

## Bundle gap inventory

Resolved markers are removed; regression assertions and historical test references remain. Failure details and physical connection/transaction metrics are in `performance-results.json`.

### PERFORMANCE-NPLUS1 — RESOLVED_ON_TESTED_REVISION

PERFORMANCE_GAP · P1 · 0 failing cases.

Expected: Bounded page 5/20 query growth and preserved plain/include/related absolute query budgets.

Bundle subsystem: Doctrine reads and tenant-safe query-plan forwarding.

Current interpretation: All six fixed release scenarios pass in the complete Torture run after explicit tenant-safe query-plan forwarding. Prior page 5/20 counts were constant; the excess was fallback overhead, not proven linear N+1. Public capability stability remains a design decision.

- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded`

### SCALABILITY-FILTER-BUDGET — RESOLVED_ON_TESTED_REVISION

SCALABILITY_GAP · P1 · 0 failing cases.

Expected: Reject excessive filter trees and operand lists before SQL.

Bundle subsystem: Criteria complexity enforcement.

- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget`

### SCALABILITY-JOIN-PAGINATION — RESOLVED_ON_TESTED_REVISION

SCALABILITY_GAP · P1 · 0 failing cases.

Expected: Paginate distinct roots across multiple to-many joins without omissions.

Bundle subsystem: Doctrine collection paginator.

- `App\Tests\Torture\Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks`

### SCALABILITY-INCLUDE-AMPLIFICATION — RESOLVED_ON_TESTED_REVISION

SCALABILITY_GAP · P1 · 0 failing cases.

Expected: Stop include traversal at the cap before mass hydration.

Bundle subsystem: Include traversal and limits.

- `App\Tests\Torture\Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration`

### ARCHITECTURE-COMPOSITE-ID — RESOLVED_ON_TESTED_REVISION

ARCHITECTURE_GAP · P1 · 0 failing cases.

Expected: Reject unsupported composite identifiers during resource/route discovery with a diagnostic.

Bundle subsystem: Doctrine metadata discovery.

- `App\Tests\Torture\Extreme\CompositeDiscoveryTest::testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery`

### CONSISTENCY-ETAG — RESOLVED_ON_TESTED_REVISION

CONSISTENCY_GAP · P0 · 0 failing cases.

Expected: Two writers with the same validator produce one success and one 412 without loser mutation.

Bundle subsystem: Write preconditions and optimistic concurrency.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly`

### CONSISTENCY-UNIQUE-RACE — RESOLVED_ON_TESTED_REVISION

CONSISTENCY_GAP · P0 · 0 failing cases.

Expected: Unique create race yields 201 and JSON:API 409, with exactly one row.

Bundle subsystem: Doctrine constraint error translation.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow`

### TRANSACTION-BOUNDARY — RESOLVED_ON_TESTED_REVISION

TRANSACTION_BOUNDARY · P0 · 0 failing cases.

Expected: Reject Atomic batches spanning independent managers/shards before mutation.

Bundle subsystem: Atomic transaction boundary discovery.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation`
- `App\Tests\Torture\Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation`

### TRANSACTION-SECOND-COMMIT — RESOLVED_ON_TESTED_REVISION

TRANSACTION_BOUNDARY · P0 · 0 failing cases.

Expected: A single-connection PostgreSQL Atomic batch commits only PostgreSQL, succeeds even when an unused MySQL commit would fail, and persists its final state.

Bundle subsystem: Transaction runner manager enlistment.

Current interpretation: The scoped transaction provider does not enlist the unrelated MySQL connection.

Cross-connection Atomic must be rejected before mutation; same-connection Atomic must preserve all-or-nothing rollback. No distributed transaction is required.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection`

