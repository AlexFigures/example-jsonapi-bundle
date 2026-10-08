# Production torture results

Generated from complete JUnit and scenario HTTP metrics on bundle `96a1530f3155ddf001b7d1e48fd33e375c382d85`.

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
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 12 | 82.882 | 54.5 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 4, 7, 5 | 15.928, 66.395, 32.849 | 54.5, 54.5, 54.5 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 24 | 46.577 | 54.5 | 7125 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 400 | 0 | 4.07 | 54.5 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 9, 3, 2, 7, 4, 1 | 41.959, 45.895, 45.525, 37.631, 33.958, 32.798, 51.918, 29.836 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 7, 10, 7 | 22.403, 106.482, 38.586 | 54.5, 54.5, 54.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 9, 1, 1 | 25.29, 40.359, 20.051 | 54.5, 54.5, 54.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200 | 13, 13, 13 | 63.544, 88.467, 82.602 | 54.5, 54.5, 54.5 | 30203, 30391, 30166 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200, 200, 200, 200, 200 | 8, 8, 8, 10, 8, 8, 8 | 32.882, 59.279, 47.642, 47.101, 73.938, 53.136, 49.046 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 2.46 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 6.016 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 9.575 | 54.5 | 225 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 2.719 | 54.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 5.419 | 54.5 | 227 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 200 | 9 | 30.185 | 54.5 | 8147 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 32.079, 39.842, 52.82, 45.221, 59.241, 47.028, 26.178, 41.45 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 24.959, 61.458, 41.35, 30.535, 34.43, 38.528, 34.74, 36.577 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 22.413, 16.455, 14.219 | 54.5, 54.5, 54.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 404, 404, 200 | 3, 1, 2, 3 | 9.817, 36.449, 26.49, 26.011 | 54.5, 54.5, 54.5, 54.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200 | 0, 3 | 1.714, 42.575 | 54.5, 54.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200, 200 | 0, 5, 5 | 2.119, 52.51, 48.156 | 54.5, 54.5, 54.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 5, 3, 3, 9, 5 | 46.468, 45.806, 30.957, 52.096, 54.485 | 62.5, 62.5, 62.5, 62.5, 68.5 | 290767, 2887, 2775, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200 | 5, 1 | 41.005, 85.375 | 68.5, 72.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 400 | 9 | 26.451 | 72.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS / PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 26.401, 102.652, 46.056, 69.088 | 72.5, 72.5, 72.5, 72.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 8, 8 | 24.153, 59.426 | 72.5, 72.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 18, 18 | 34.222, 93.818 | 72.5, 72.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 14, 14 | 44.204, 99.017 | 72.5, 72.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 26, 26 | 65.571, 127.391 | 72.5, 72.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 9, 9 | 26.128, 72.487 | 72.5, 72.5 | 8194, 30889 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200 | 2 | 7.861 | 72.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 15, 6, 15, 10, 15 | 41.648, 51.146, 59.891, 34.661, 71.74 | 72.5, 72.5, 72.5, 72.5, 72.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 304, 200 | 7, 10, 7, 7 | 19.959, 16.847, 12.744, 10.176 | 72.5, 72.5, 72.5, 72.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 21.898, 23.704, 10.904 | 72.5, 72.5, 72.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / INFRASTRUCTURE_LIMIT | 200 | 5 | 47.797 | 72.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201 | 8 | 50.719 | 72.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 412, 200, 200 | 7, 7, 16, 7 | 19.418, 236.222, 180.729, 53.174 | 72.5, 22, 22, 72.5 | 1553, 239, 1557, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 201, 200 | 3, 7, 6 | 124.866, 151.358, 19.466 | 20, 22, 72.5 | 191, 1238, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 200 | 0, 5 | 4.847, 47.074 | 72.5, 72.5 | 336, 2536 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 500 | 0 | 4.18 | 72.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200 | 6, 5 | 44.006, 53.706 | 72.5, 72.5 | 2464, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.598, 42.105 | 72.5, 72.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 4.186, 49.504 | 72.5, 72.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.558, 50.238 | 72.5, 72.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200 | 10 | 104.456 | 93.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400 | 0 | 6.772 | 72.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / APPLICATION_POLICY | 413, 413 | 0, 0 | 1.94, 2.975 | 74.5, 74.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200, 400, 200 | 6, 0, 5 | 43.739, 4.051, 38.297 | 72.5, 72.5, 72.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200, 200 | 5, 8, 5, 5 | 41.392, 54.881, 29.395, 29.868 | 72.5, 72.5, 72.5, 72.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200 | 9, 3, 3 | 44.542, 39.593, 26.394 | 72.5, 72.5, 72.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200 | 6, 5 | 58.323, 45.415 | 72.5, 72.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 201, 404, 204, 404 | 8, 1, 4, 1 | 43.546, 33.563, 34.872, 24.233 | 72.5, 72.5, 72.5, 72.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / APPLICATION_POLICY | 200, 200, 200 | 8, 5, 5 | 58.927, 5.96, 48.282 | 72.5, 72.5, 72.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 406, 200, 200, 200 | 5, 0, 7, 24, 7 | 14.06, 0.951, 14.791, 41.826, 10.648 | 72.5, 72.5, 72.5, 72.5, 72.5 | 601, 245, 1553, 7125, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 400, 200 | 3, 0, 3 | 6.652, 1.036, 2.578 | 72.5, 72.5, 72.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 15.592, 42.719, 37.363 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 12.447, 45.253, 29.597 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 11.382, 42.052, 43.127 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200 | 4, 7 | 105.618, 45.48 | 72.5, 72.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 409, 200, 200 | 0, 5, 1 | 2.59, 41.357, 24.898 | 72.5, 72.5, 72.5 | 336, 2536, 243 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 200, 200, 200, 200 | 10, 10, 10, 3 | 49.403, 118.497, 133.3, 35.129 | 72.5, 18, 18, 72.5 | 139, 164, 189, 243 | 100, 100, 100, 100 |

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

