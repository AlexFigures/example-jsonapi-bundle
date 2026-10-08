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
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 12 | 96.765 | 54.5 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 4, 7, 5 | 14.726, 53.964, 64.93 | 54.5, 54.5, 54.5 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 24 | 50.424 | 54.5 | 7125 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 400 | 0 | 4.605 | 54.5 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 9, 3, 2, 7, 4, 1 | 48.282, 52.155, 36.771, 27.773, 34.373, 31.192, 54.639, 24.337 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 7, 10, 7 | 21.013, 85.487, 42.775 | 54.5, 54.5, 54.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 9, 1, 1 | 21.49, 30.022, 24.509 | 54.5, 54.5, 54.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200 | 13, 13, 13 | 58.46, 88.851, 69.181 | 54.5, 54.5, 54.5 | 30203, 30391, 30166 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200, 200, 200, 200, 200 | 8, 8, 8, 10, 8, 8, 8 | 32.032, 67.426, 50.722, 53.974, 47.889, 46.408, 49.82 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 3.997 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 6.747 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 8.493 | 56.5 | 225 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 2.521 | 54.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 4.089 | 54.5 | 227 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 200 | 9 | 28.499 | 54.5 | 8147 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 24.463, 41.417, 89.302, 61.182, 44.985, 49.596, 32.13, 25.959 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 28.936, 40.777, 38.908, 40.67, 45.093, 39.145, 31.253, 48.718 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 23.022, 19.431, 10.035 | 56.5, 56.5, 56.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 404, 404, 200 | 3, 1, 2, 3 | 9.868, 37, 23.586, 28.007 | 56.5, 56.5, 56.5, 56.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200 | 0, 3 | 1.432, 33.525 | 56.5, 56.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200, 200 | 0, 5, 5 | 1.662, 47.123, 38.425 | 56.5, 56.5, 56.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 5, 3, 3, 9, 5 | 76.02, 62.041, 37.364, 48.617, 60.57 | 62.5, 62.5, 62.5, 62.5, 68.5 | 290767, 2887, 2775, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200 | 5, 1 | 32.384, 76.884 | 68.5, 72.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 400 | 9 | 29.738 | 72.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS / PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 29, 53.697, 50.213, 74.332 | 72.5, 72.5, 72.5, 72.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 8, 8 | 22.216, 62.941 | 72.5, 72.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 18, 18 | 50.512, 97.613 | 72.5, 72.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 14, 14 | 39.702, 99.984 | 72.5, 72.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 26, 26 | 63.062, 126.586 | 72.5, 72.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 9, 9 | 26.453, 59.875 | 72.5, 72.5 | 8194, 30889 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200 | 2 | 9.325 | 72.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 15, 6, 15, 10, 15 | 25.292, 45.219, 58.403, 51.939, 70.33 | 72.5, 72.5, 72.5, 72.5, 72.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 304, 200 | 7, 10, 7, 7 | 21.802, 14.852, 14.836, 8.596 | 72.5, 72.5, 72.5, 72.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 17.097, 18.954, 12.881 | 72.5, 72.5, 72.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / INFRASTRUCTURE_LIMIT | 200 | 5 | 58.82 | 72.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201 | 8 | 71.954 | 72.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 412, 200 | 7, 16, 7, 7 | 20.031, 260.53, 346.82, 37.767 | 72.5, 22, 22, 72.5 | 1553, 1557, 239, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201, 409, 200 | 7, 3, 6 | 187.996, 153.871, 17.444 | 22, 20, 72.5 | 1238, 191, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 200 | 0, 5 | 5.734, 32.083 | 72.5, 72.5 | 336, 2536 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 500 | 0 | 5.411 | 72.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200 | 6, 5 | 7.05, 38.462 | 72.5, 72.5 | 2464, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.534, 51.285 | 72.5, 72.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 4.219, 49.255 | 72.5, 72.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 3.088, 52.591 | 72.5, 72.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200 | 10 | 135.61 | 93.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400 | 0 | 9.342 | 72.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / APPLICATION_POLICY | 413, 413 | 0, 0 | 2.375, 2.852 | 74.5, 74.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200, 400, 200 | 6, 0, 5 | 59.203, 4.188, 37.101 | 72.5, 72.5, 72.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200, 200 | 5, 8, 5, 5 | 46.64, 45.71, 38.696, 37.742 | 72.5, 72.5, 72.5, 72.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200 | 9, 3, 3 | 45.552, 41.507, 31.728 | 72.5, 72.5, 72.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200 | 6, 5 | 52.438, 44.333 | 72.5, 72.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 201, 404, 204, 404 | 8, 1, 4, 1 | 44.443, 30.104, 24.157, 25.106 | 72.5, 72.5, 72.5, 72.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / APPLICATION_POLICY | 200, 200, 200 | 8, 5, 5 | 54.878, 6.246, 45.462 | 72.5, 72.5, 72.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 406, 200, 200, 200 | 5, 0, 7, 24, 7 | 15.993, 0.868, 12.121, 33.055, 9.517 | 72.5, 72.5, 72.5, 72.5, 72.5 | 601, 245, 1553, 7125, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 400, 200 | 3, 0, 3 | 5.424, 1.43, 2.427 | 72.5, 72.5, 72.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 12.161, 47.722, 38.619 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 14.97, 39.884, 37.236 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 13.564, 44.602, 33.059 | 72.5, 72.5, 72.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200 | 4, 7 | 105.91, 48.922 | 72.5, 72.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 409, 200, 200 | 0, 5, 1 | 2.897, 39.587, 27.616 | 72.5, 72.5, 72.5 | 336, 2536, 243 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 200, 200, 200, 200 | 10, 10, 10, 3 | 11.203, 144.967, 147.362, 27.17 | 72.5, 18, 18, 72.5 | 139, 189, 189, 243 | 100, 100, 100, 100 |

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

