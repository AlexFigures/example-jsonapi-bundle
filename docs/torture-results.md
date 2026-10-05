# Production torture results

Generated from complete JUnit and scenario HTTP metrics on bundle `1a32d7b35ca5ce3e7c4500f52a2455655fa57c6b`.

- HTTP test outcome PASS: 57
- HTTP test outcome FAIL: 5
- PASS: 52
- BUNDLE_GAP: 5
- APPLICATION_POLICY: 4
- INFRASTRUCTURE_LIMIT: 1
- UNEXPECTED_FAILURE: 0
- SKIPPED: 0

Wall times are observations, not CI thresholds. Memory is the PHP process peak since request start; baseline includes loaded classes. Rows fetched measures DBAL fetches, not exact ORM hydration. Routing records actual physical database names. Dataset counts exclude the extra BIGINT row.

## Scenario matrix

| Scenario | Result | HTTP | SQL counts | Wall ms | Peak MiB | Response bytes | Dataset tasks |
|---|---|---|---|---|---|---|---|
| [Extreme\CompositeDiscoveryTest::testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery](../tests/Torture/Extreme/CompositeDiscoveryTest.php) | PASS / PASS |  |  |  |  |  |  |
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 26 | 151.246 | 50.5 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 8, 15, 9 | 45.495, 105.52, 65.366 | 50.5, 52.5, 52.5 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 72 | 203.618 | 52.5 | 7125 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 400 | 0 | 8.483 | 52.5 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 9, 3, 2, 7, 4, 1 | 37.207, 43.866, 56.132, 49.238, 39.156, 54.666, 34.589, 29.32 | 52.5, 52.5, 52.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 19, 22, 19 | 68.42, 126.643, 96.827 | 54.5, 54.5, 54.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 21, 1, 1 | 87.924, 39.428, 41.906 | 54.5, 54.5, 54.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200 | 27, 27, 27 | 173.81, 210.55, 189.137 | 54.5, 54.5, 56.5 | 30203, 30391, 30166 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200, 200, 200, 200, 200 | 20, 20, 20, 22, 20, 20, 20 | 87.269, 159.223, 124.368, 106.038, 134.879, 151.721, 131.066 | 56.5, 56.5, 56.5, 56.5, 56.5, 56.5, 56.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 11.173 | 56.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 15.195 | 56.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 27.829 | 56.5 | 225 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 8.255 | 56.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 11.471 | 56.5 | 227 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 200 | 21 | 105.69 | 56.5 | 8147 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 29, 14, 16, 16, 9, 9, 1, 4 | 88.351, 98.87, 85.067, 74.404, 53.299, 60.525, 34.779, 38.189 | 56.5, 56.5, 56.5, 56.5, 56.5, 56.5, 56.5, 56.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 29, 14, 16, 16, 9, 9, 1, 4 | 54.7, 84.681, 89.658, 81.203, 46.46, 55.821, 42.804, 39.215 | 56.5, 56.5, 56.5, 56.5, 56.5, 56.5, 56.5, 56.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 200 | 21, 21, 21 | 56.821, 63.541, 40.147 | 56.5, 56.5, 56.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 404, 404, 200 | 7, 1, 2, 3 | 30.656, 50.381, 43.077, 45.692 | 56.5, 56.5, 56.5, 56.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200 | 0, 3 | 6.955, 57.263 | 56.5, 56.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200, 200 | 0, 13, 13 | 6.501, 97.165, 95.581 | 56.5, 56.5, 56.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 13, 3, 3, 9, 13 | 330.088, 57.801, 61.231, 29.249, 379.675 | 66.5, 66.5, 66.5, 66.5, 72.5 | 290767, 2887, 2775, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200 | 13, 1 | 337.591, 139.42 | 74.5, 78.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 400 | 20 | 94.814 | 78.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS / PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 47.729, 70.273, 58.096, 91.726 | 78.5, 78.5, 78.5, 78.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | FAIL / BUNDLE_GAP | 200, 200 | 20, 20 | 90.325, 163.096 | 78.5, 78.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | FAIL / BUNDLE_GAP | 200, 200 | 42, 42 | 171.729, 228.734 | 78.5, 78.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | FAIL / BUNDLE_GAP | 200, 200 | 28, 28 | 132.716, 223.461 | 78.5, 78.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | FAIL / BUNDLE_GAP | 200, 200 | 58, 58 | 229.236, 339.617 | 78.5, 78.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | FAIL / BUNDLE_GAP | 200 | 21 | 111.46 | 78.5 | 30889 | 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200 | 2 | 20.802 | 78.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 33, 10, 33, 10, 33 | 92.028, 66.839, 142.765, 40.534, 145.898 | 78.5, 78.5, 78.5, 78.5, 78.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 304, 200 | 19, 22, 19, 19 | 78.595, 41.374, 41.291, 38.585 | 78.5, 78.5, 78.5, 78.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 200 | 21, 21, 21 | 70.559, 66.297, 38.944 | 78.5, 78.5, 78.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / INFRASTRUCTURE_LIMIT | 200 | 13 | 95.987 | 78.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201 | 16 | 92.307 | 78.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 412, 200 | 19, 40, 19, 19 | 82.027, 305.035, 411.032, 103.637 | 78.5, 22, 22, 78.5 | 1553, 1557, 239, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 201, 200 | 3, 15, 14 | 148.257, 215.463, 49.327 | 20, 22, 78.5 | 191, 1238, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 200 | 0, 13 | 11.572, 93.362 | 78.5, 78.5 | 320, 2536 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 500 | 0 | 9.834 | 78.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200 | 6, 13 | 32.928, 85.338 | 78.5, 78.5 | 2464, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 19 | 9.897, 108.512 | 78.5, 78.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 19 | 8.469, 113.087 | 78.5, 78.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 19 | 10.694, 118.036 | 78.5, 78.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200 | 22 | 189.256 | 99.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400 | 0 | 26.29 | 78.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / APPLICATION_POLICY | 413, 413 | 0, 0 | 3.733, 4.87 | 82.5, 80.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200, 400, 200 | 6, 0, 13 | 133.315, 7.491, 96.403 | 78.5, 78.5, 78.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200, 200 | 13, 16, 13, 13 | 94.514, 106.634, 83.786, 90.199 | 78.5, 78.5, 78.5, 78.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200 | 9, 3, 3 | 63.67, 61.479, 51.106 | 78.5, 78.5, 78.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200 | 6, 13 | 78.055, 95.028 | 78.5, 78.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 201, 404, 204, 404 | 16, 1, 4, 1 | 108.052, 46.721, 37.022, 38.378 | 78.5, 78.5, 78.5, 78.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / APPLICATION_POLICY | 200, 200, 200 | 16, 13, 13 | 107.894, 25.949, 94.522 | 78.5, 78.5, 78.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 406, 200, 200, 200 | 9, 0, 19, 72, 19 | 42.389, 1.589, 62.441, 173.51, 39.406 | 78.5, 78.5, 78.5, 78.5, 78.5 | 601, 245, 1553, 7125, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 400, 200 | 3, 0, 3 | 15.505, 3.014, 7.114 | 78.5, 78.5, 78.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 13, 13 | 40.902, 101.362, 87.504 | 78.5, 78.5, 78.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 13, 13 | 40.867, 91.122, 88.613 | 78.5, 78.5, 78.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 13, 13 | 41.661, 103.199, 72.647 | 78.5, 78.5, 78.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200 | 4, 19 | 113.806, 123.736 | 78.5, 78.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 409, 200, 200 | 0, 13, 1 | 8.473, 100.228, 40.597 | 78.5, 78.5, 78.5 | 320, 2536, 243 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 200, 200, 200, 200 | 10, 10, 10, 3 | 28.669, 233.852, 211.982, 44.764 | 78.5, 20, 20, 78.5 | 139, 189, 164, 243 | 100, 100, 100, 100 |

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

