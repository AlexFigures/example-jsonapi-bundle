# Production torture results

Generated from complete JUnit and scenario HTTP metrics on bundle `f02849d58615e29d20c4e14fb373a3ca0a1db94e`.

- PASS: 40
- BUNDLE_GAP: 17
- APPLICATION_POLICY: 4
- INFRASTRUCTURE_LIMIT: 1
- UNEXPECTED_FAILURE: 0
- SKIPPED: 0

Wall times are observations, not CI thresholds. Memory is the PHP process peak since request start; baseline includes loaded classes. Rows fetched measures DBAL fetches, not exact ORM hydration. Routing records actual physical database names. Dataset counts exclude the extra BIGINT row.

## Scenario matrix

| Scenario | Result | HTTP | SQL counts | Wall ms | Peak MiB | Response bytes | Dataset tasks |
|---|---|---|---|---|---|---|---|
| [Extreme\CompositeDiscoveryTest::testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery](../tests/Torture/Extreme/CompositeDiscoveryTest.php) | BUNDLE_GAP |  |  |  |  |  |  |
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 200 | 8 | 50.351 | 30 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 200, 200, 200 | 2, 4, 2 | 23.991, 63.145, 37.165 | 34, 34, 34 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 200 | 17 | 33.841 | 34 | 8507 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 400 | 0 | 10.191 | 34 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 7, 2, 2, 5, 4, 1 | 84.591, 129.488, 118.226, 34.342, 30.113, 114.414, 90.112, 25.582 | 38.5, 38.5, 38.5, 38.5, 38.5, 38.5, 38.5, 38.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 200, 200, 200 | 4, 7, 4 | 14.033, 134.083, 40.711 | 38.5, 38.5, 38.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS | 200, 200, 200 | 4, 1, 1 | 17.736, 41.585, 28.64 | 38.5, 38.5, 38.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | BUNDLE_GAP | 200 | 28 | 70.508 | 38.5 | 16442 | 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS | 200, 200, 200, 200, 200, 200, 200 | 62, 62, 62, 7, 62, 62, 62 | 102.075, 144.35, 138.34, 458.713, 109.284, 118.83, 103.808 | 38.5, 40.5, 40.5, 40.5, 40.5, 40.5, 40.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | BUNDLE_GAP | 200 | 17 | 83.949 | 40.5 | 24919 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | BUNDLE_GAP | 200 | 17 | 672.364 | 42.5 | 96919 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | BUNDLE_GAP | 200 | 17 | 2288.712 | 44.5 | 186919 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS | 400 | 0 | 7.156 | 44.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | BUNDLE_GAP | 200 | 17 | 63.503 | 44.5 | 96919 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | BUNDLE_GAP | 200 | 11 | 33.824 | 44.5 | 5383 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 6, 4, 4, 6, 7, 2, 1, 4 | 30.945, 44.923, 126.19, 108.338, 110.622, 22.116, 32.933, 111.373 | 44.5, 44.5, 44.5, 44.5, 44.5, 44.5, 44.5, 44.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 6, 4, 4, 6, 7, 2, 1, 4 | 26.681, 48.458, 132.464, 547.959, 118.547, 24.115, 37.809, 146.109 | 44.5, 44.5, 44.5, 44.5, 44.5, 44.5, 44.5, 44.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS | 200, 200, 200 | 6, 6, 6 | 26.053, 21.474, 11.577 | 44.5, 44.5, 44.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS | 200, 404, 404, 200 | 1, 1, 2, 1 | 9.134, 37.656, 27.858, 26.822 | 44.5, 44.5, 44.5, 44.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | APPLICATION_POLICY | 409, 200 | 0, 1 | 5.108, 33.96 | 44.5, 44.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | APPLICATION_POLICY | 409, 200, 200 | 0, 3, 3 | 3.445, 56.591, 50.922 | 44.5, 44.5, 44.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS | 200, 200, 200, 200, 200 | 3, 2, 2, 7, 3 | 1285.884, 1407.336, 1244.821, 177.211, 1312.261 | 72.5, 76.5, 76.5, 74.5, 80.5 | 290767, 2887, 2730, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS | 200, 200 | 3, 1 | 1275.51, 47.604 | 82.5, 80.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | BUNDLE_GAP | 400 | 3176 | 5685.252 | 90.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 49.684, 65.168, 64.643, 94.351 | 90.5, 90.5, 90.5, 90.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | BUNDLE_GAP | 200, 200 | 17, 62 | 37.761, 132.607 | 90.5, 90.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | BUNDLE_GAP | 200, 200 | 19, 64 | 121.888, 302.041 | 90.5, 90.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | BUNDLE_GAP | 200, 200 | 22, 52 | 70.983, 176.395 | 90.5, 90.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | BUNDLE_GAP | 200, 200 | 21, 66 | 168.233, 427.723 | 90.5, 90.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | BUNDLE_GAP | 200 | 62 | 160.465 | 90.5 | 30844 | 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS | 200 | 2 | 15.343 | 90.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS | 200, 200, 200, 200, 200 | 10, 4, 10, 8, 9 | 27.477, 150.098, 60.935, 113.262, 57.889 | 90.5, 90.5, 90.5, 90.5, 90.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS | 200, 200, 304, 200 | 4, 4, 4, 4 | 18.285, 6.867, 5.378, 5.654 | 90.5, 90.5, 90.5, 90.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS | 200, 200, 200 | 6, 6, 6 | 24.532, 22.9, 16.914 | 90.5, 90.5, 90.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | INFRASTRUCTURE_LIMIT | 200 | 3 | 57.279 | 90.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS | 201 | 4 | 74.746 | 90.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | BUNDLE_GAP | 200, 200, 200, 200 | 4, 7, 7, 4 | 16.879, 230.614, 225.413, 39.987 | 90.5, 20, 20, 90.5 | 1553, 1557, 1557, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS | 409, 201, 200 | 3, 3, 4 | 221.482, 227.363, 14.022 | 20, 20, 90.5 | 191, 1238, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | BUNDLE_GAP | 200, 200 | 10, 3 | 101.789, 45.959 | 90.5, 90.5 | 2642, 2532 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS | 500 | 0 | 10.323 | 90.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSecondManagerCommitFailureLeavesNoPartialMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | BUNDLE_GAP | 500, 200 | 6, 3 | 63.681, 42.346 | 90.5, 90.5 | 177, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 400, 200 | 0, 4 | 7.276, 54.419 | 90.5, 90.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 400, 200 | 0, 4 | 6.622, 51.706 | 90.5, 90.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 400, 200 | 0, 4 | 7.219, 46.011 | 90.5, 90.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 200 | 7 | 126.873 | 111.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 400 | 0 | 18.63 | 92.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | APPLICATION_POLICY | 413, 413 | 0, 0 | 2.541, 4.852 | 94.5, 92.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS | 200, 400, 200 | 6, 0, 3 | 173.861, 10.826, 48.604 | 90.5, 92.5, 90.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 200, 200, 200, 200 | 3, 6, 3, 3 | 49.509, 165.757, 42.931, 47.499 | 90.5, 90.5, 90.5, 90.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 200, 200, 200 | 7, 1, 1 | 54.441, 35.279, 29.805 | 90.5, 90.5, 90.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 200, 200 | 6, 3 | 106.175, 39.758 | 90.5, 90.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 201, 404, 204, 404 | 4, 1, 4, 1 | 62.843, 27.922, 112.543, 21.955 | 90.5, 90.5, 90.5, 90.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | APPLICATION_POLICY | 200, 200, 200 | 6, 3, 3 | 75.207, 10.706, 51.668 | 90.5, 90.5, 90.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 200, 406, 200, 200, 200 | 2, 0, 4, 17, 4 | 13.301, 1.389, 9.454, 22.878, 6.392 | 90.5, 90.5, 90.5, 90.5, 90.5 | 601, 245, 1553, 8507, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS | 200, 400, 200 | 3, 0, 3 | 76.877, 5.631, 20.339 | 90.5, 90.5, 90.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS | 500, 200, 200 | 10, 3, 3 | 81.492, 53.558, 43.274 | 90.5, 90.5, 90.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS | 500, 200, 200 | 10, 3, 3 | 82.345, 41.202, 46.472 | 90.5, 90.5, 90.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS | 500, 200, 200 | 10, 3, 3 | 82.561, 47.221, 34.998 | 90.5, 90.5, 90.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS | 500, 200 | 4, 4 | 142.82, 43.205 | 90.5, 90.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | BUNDLE_GAP | 200, 200, 200 | 10, 3, 1 | 91.817, 40.682, 39.253 | 90.5, 90.5, 90.5 | 2639, 2542, 253 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS | 200, 200, 200, 200 | 8, 8, 8, 2 | 106.478, 209.816, 213.89, 39.672 | 90.5, 20, 20, 90.5 | 139, 164, 189, 243 | 100, 100, 100, 100 |

## Bundle gap inventory

Passing historical markers are retained as evidence, not counted as open gaps. Failure details and physical connection/transaction metrics are in `performance-results.json`.

### PERFORMANCE-NPLUS1 — OPEN

PERFORMANCE_GAP · P1 · 5 failing cases.

Expected: SQL count stays approximately constant when page size grows from 5 to 20.

Bundle subsystem: Doctrine reads and linkage batching.

- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"`
- `App\Tests\Torture\Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded`

### SCALABILITY-FILTER-BUDGET — OPEN

SCALABILITY_GAP · P1 · 4 failing cases.

Expected: Reject excessive filter trees and operand lists before SQL.

Bundle subsystem: Criteria complexity enforcement.

- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget`

### SCALABILITY-JOIN-PAGINATION — OPEN

SCALABILITY_GAP · P1 · 2 failing cases.

Expected: Paginate distinct roots across multiple to-many joins without omissions.

Bundle subsystem: Doctrine collection paginator.

- `App\Tests\Torture\Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots`
- `App\Tests\Torture\Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks`

### SCALABILITY-INCLUDE-AMPLIFICATION — OPEN

SCALABILITY_GAP · P1 · 1 failing cases.

Expected: Stop include traversal at the cap before mass hydration.

Bundle subsystem: Include traversal and limits.

- `App\Tests\Torture\Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration`

### ARCHITECTURE-COMPOSITE-ID — OPEN

ARCHITECTURE_GAP · P1 · 1 failing cases.

Expected: Reject unsupported composite identifiers during resource/route discovery with a diagnostic.

Bundle subsystem: Doctrine metadata discovery.

- `App\Tests\Torture\Extreme\CompositeDiscoveryTest::testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery`

### CONSISTENCY-ETAG — OPEN

CONSISTENCY_GAP · P0 · 1 failing cases.

Expected: Two writers with the same validator produce one success and one 412 without loser mutation.

Bundle subsystem: Write preconditions and optimistic concurrency.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly`

### CONSISTENCY-UNIQUE-RACE — RESOLVED_ON_TESTED_REVISION

CONSISTENCY_GAP · P0 · 0 failing cases.

Expected: Unique create race yields 201 and JSON:API 409, with exactly one row.

Bundle subsystem: Doctrine constraint error translation.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow`

### TRANSACTION-BOUNDARY — OPEN

TRANSACTION_BOUNDARY · P0 · 2 failing cases.

Expected: Reject Atomic batches spanning independent managers/shards before mutation.

Bundle subsystem: Atomic transaction boundary discovery.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation`
- `App\Tests\Torture\Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation`

### TRANSACTION-SECOND-COMMIT — OPEN

TRANSACTION_BOUNDARY · P0 · 1 failing cases.

Expected: Single-manager Atomic does not commit unrelated managers or suffer a partial commit on their failure.

Bundle subsystem: Transaction runner manager enlistment.

- `App\Tests\Torture\Chaos\ConsistencyAndFailureTest::testSecondManagerCommitFailureLeavesNoPartialMutation`

