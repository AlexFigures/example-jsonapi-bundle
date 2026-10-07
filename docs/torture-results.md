# Production torture results

Generated from complete JUnit and scenario HTTP metrics on bundle `a17ffd40a7d3a1e642a33aaf788427bb6b117fdb`.

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
| [Extreme\DomainTopologyTest::testAssociationEntityHasStateAndNestedUserInclude](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 12 | 87.691 | 52.5 | 7832 | 100 |
| [Extreme\DomainTopologyTest::testMembershipFilterSortAndTraversal](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 4, 7, 5 | 15.346, 54.716, 31.418 | 52.5, 52.5, 52.5 | 3816, 3016, 697 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testSelfGraphTerminatesAndDeduplicates](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200 | 24 | 44.994 | 52.5 | 7125 | 100 |
| [Extreme\DomainTopologyTest::testIncludeDepthRejectedBeforeSql](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 400 | 0 | 3.435 | 52.5 | 209 | 100 |
| [Extreme\DomainTopologyTest::testNaturalIdentifierCrudQueryAndRelationship](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 201, 200, 200, 200, 200, 200, 204, 404 | 4, 4, 9, 3, 2, 7, 4, 1 | 15.41, 34.826, 41.385, 33.367, 42.249, 27.919, 27.643, 26.293 | 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 52.5 | 211, 223, 156, 239, 589, 130, 0, 250 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\DomainTopologyTest::testBigintIdentifierIsLossless](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 7, 10, 7 | 19.415, 75.679, 46.554 | 52.5, 52.5, 52.5 | 1482, 1481, 1481 | 100, 100, 100 |
| [Extreme\DomainTopologyTest::testInheritanceIdentityAndIncludedSubclasses](../tests/Torture/Extreme/DomainTopologyTest.php) | PASS / PASS | 200, 200, 200 | 9, 1, 1 | 25.355, 38.186, 24.1 | 52.5, 52.5, 52.5 | 1841, 231, 219 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testCartesianJoinsPaginateDistinctRoots](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200 | 13, 13, 13 | 73.026, 85.148, 84.819 | 52.5, 52.5, 52.5 | 30203, 30391, 30166 | 100, 100, 100 |
| [Extreme\PaginationUnderJoinsTest::testEqualSortValuesStayStableAcrossUnrelatedMutation](../tests/Torture/Extreme/PaginationUnderJoinsTest.php) | PASS / PASS | 200, 200, 200, 200, 200, 200, 200 | 8, 8, 8, 10, 8, 8, 8 | 36.854, 65.041, 64.774, 47.892, 56.567, 63.641, 58.001 | 52.5, 52.5, 52.5, 52.5, 52.5, 52.5, 54.5 | 30978, 31061, 31061, 1629, 30978, 31061, 31061 | 1000, 1000, 1000, 1000, 1000, 1000, 1000 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "100 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 4.144 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "500 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 6.539 | 54.5 | 224 | 100 |
| [Extreme\QueryAmplificationTest::testFilterNodeBudgetRejectsBeforeSql with data set "1000 nodes"](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 8.982 | 54.5 | 225 | 100 |
| [Extreme\QueryAmplificationTest::testFilterDepthBudgetRejectsBeforeSql](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 3.031 | 54.5 | 208 | 100 |
| [Extreme\QueryAmplificationTest::testLargeInListCannotBypassComplexityBudget](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 400 | 0 | 4.653 | 54.5 | 227 | 100 |
| [Extreme\QueryAmplificationTest::testReasonableBooleanAndRelationshipFilterWorks](../tests/Torture/Extreme/QueryAmplificationTest.php) | PASS / PASS | 200 | 9 | 32.78 | 54.5 | 8147 | 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "A"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 30.548, 39.598, 54.118, 34.324, 52.394, 32.886, 33.394, 27.735 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4114, 3092, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testShardCrudQueriesAndRelationships with data set "B"](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 201, 200, 200, 200, 404, 204 | 13, 6, 8, 8, 9, 5, 1, 4 | 32.773, 51.027, 40.016, 44.193, 29.414, 38.67, 32.145, 33.662 | 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5, 54.5 | 4151, 3082, 1025, 1038, 162, 695, 258, 0 | 100, 100, 100, 100, 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 19.797, 15.588, 9.61 | 54.5, 54.5, 54.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Extreme\TenantIsolationTest::testRelationshipLookupCannotResolveAnotherTenantsIdentifier](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / PASS | 200, 404, 404, 200 | 3, 1, 2, 3 | 11.272, 27.933, 30.311, 25.483 | 54.5, 54.5, 54.5, 54.5 | 689, 260, 302, 212 | 100, 100, 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardRelationshipIsRejectedBeforePersistence](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200 | 0, 3 | 1.981, 32.87 | 54.5, 54.5 | 204, 212 | 100, 100 |
| [Extreme\TenantIsolationTest::testCrossShardAtomicIsRejectedBeforeFirstMutation](../tests/Torture/Extreme/TenantIsolationTest.php) | PASS / APPLICATION_POLICY | 409, 200, 200 | 0, 5, 5 | 2.387, 46.248, 44.231 | 54.5, 54.5, 54.5 | 204, 2536, 2534 | 100, 100, 100 |
| [Performance\HighCardinalityTest::testTenThousandLinkageIdentifiersAndMutation](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 5, 3, 3, 9, 5 | 45.729, 47.571, 44.869, 35.435, 74.259 | 62.5, 62.5, 62.5, 62.5, 66.5 | 290767, 2887, 2775, 148, 290797 | 20000, 20000, 20000, 20000, 20000 |
| [Performance\HighCardinalityTest::testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 200, 200 | 5, 1 | 43.664, 72.746 | 68.5, 70.5 | 290767, 877 | 20000, 20000 |
| [Performance\HighCardinalityTest::testDenseIncludeIsRejectedBeforeMassHydration](../tests/Torture/Performance/HighCardinalityTest.php) | PASS / PASS | 400 | 9 | 20.73 | 70.5 | 221 | 1000 |
| [Performance\LargeDatasetBenchmarkTest::testConfigurableLargeDatasetBenchmark](../tests/Torture/Performance/LargeDatasetBenchmarkTest.php) | PASS / PASS | 200, 200, 200, 200 | 2, 2, 2, 2 | 29.572, 60.921, 54.275, 73.307 | 70.5, 70.5, 70.5, 70.5 | 2864, 3010, 3054, 3098 | 100000, 100000, 100000, 100000 |
| [Performance\NPlusOneAndCardinalityTest::testCollectionWithoutIncludeHasBoundedQueryShape](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 8, 8 | 29.673, 66.146 | 70.5, 70.5 | 8083, 30778 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 18, 18 | 57.964, 92.269 | 70.5, 70.5 | 26360, 57922 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 14, 14 | 50.02, 90.31 | 70.5, 70.5 | 12868, 40273 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested"](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 26, 26 | 72.422, 141.51 | 70.5, 70.5 | 40872, 63567 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testRelatedCollectionQueryCountIsBounded](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200, 200 | 9, 9 | 30.933, 59.754 | 70.5, 70.5 | 8194, 30889 | 1000, 1000 |
| [Performance\NPlusOneAndCardinalityTest::testSparseCollectionAvoidsUnrequestedRelationshipHydration](../tests/Torture/Performance/NPlusOneAndCardinalityTest.php) | PASS / PASS | 200 | 2 | 8.088 | 70.5 | 2713 | 1000 |
| [Chaos\CompoundCacheTest::testCompoundRepresentationTracksRelatedMutation](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 200, 200, 200 | 15, 6, 15, 10, 15 | 39.39, 54.028, 67.661, 30.424, 66.097 | 70.5, 70.5, 70.5, 70.5, 70.5 | 3743, 697, 3753, 226, 3412 | 100, 100, 100, 100, 100 |
| [Chaos\CompoundCacheTest::testProfileRepresentationAndConditionalStateDoNotLeakInWorker](../tests/Torture/Chaos/CompoundCacheTest.php) | PASS / PASS | 200, 200, 304, 200 | 7, 10, 7, 7 | 18.002, 16.901, 11.894, 6.863 | 70.5, 70.5, 70.5, 70.5 | 1553, 1610, 0, 1553 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testSameWorkerDoesNotLeakIncludeOrTenantState](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200, 200 | 9, 9, 9 | 24.151, 20.209, 14.467 | 70.5, 70.5, 70.5 | 3520, 3557, 3520 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReadReplicaTopologyIsObservableAndLagIsDocumented](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / INFRASTRUCTURE_LIMIT | 200 | 5 | 46.053 | 70.5 | 2536 | 100 |
| [Chaos\ConsistencyAndFailureTest::testWriteUsesPrimaryAndResponseIsFresh](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201 | 8 | 46.422 | 70.5 | 1015 | 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentIfMatchAllowsOneWriterOnly](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 412, 200, 200 | 7, 7, 16, 7 | 18.376, 221.339, 170.262, 48.207 | 70.5, 22, 22, 70.5 | 1553, 239, 1557, 1557 | 100, 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testConcurrentUniqueCreateHasOneConflictAndOneRow](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 201, 409, 200 | 7, 3, 6 | 141.858, 118.875, 18.417 | 22, 20, 70.5 | 1238, 191, 1552 | 100, 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testCrossManagerAtomicRejectedBeforeFirstMutation](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 409, 200 | 0, 5 | 5.061, 52.83 | 70.5, 70.5 | 336, 2536 | 100, 100 |
| [Chaos\ConsistencyAndFailureTest::testReplicaFailureIsControlled](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 500 | 0 | 4.486 | 70.5 | 177 | 100 |
| [Chaos\ConsistencyAndFailureTest::testSingleManagerAtomicDoesNotCommitUnrelatedConnection](../tests/Torture/Chaos/ConsistencyAndFailureTest.php) | PASS / PASS | 200, 200 | 6, 5 | 13.342, 41.354 | 70.5, 70.5 | 2464, 2529 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "invalid UTF-8"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 3.786, 51.003 | 70.5, 70.5 | 220, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "excessive nesting"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 3.584, 52.589 | 70.5, 70.5 | 192, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testMalformedInputFailsAtHttpBoundary with data set "huge integer identifier"](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400, 200 | 0, 7 | 2.788, 57.633 | 70.5, 70.5 | 217, 1553 | 100, 100 |
| [Chaos\HttpBoundaryTest::testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200 | 10 | 108.737 | 91.6 | 7921544 | 100 |
| [Chaos\HttpBoundaryTest::testManyUnknownMembersHaveControlledError](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 400 | 0 | 9.235 | 70.5 | 280036 | 100 |
| [Chaos\HttpBoundaryTest::testOptInIngressLimitsRejectBeforeSql](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / APPLICATION_POLICY | 413, 413 | 0, 0 | 1.658, 2.468 | 72.5, 72.5 | 161, 181 | 100, 100 |
| [Chaos\HttpBoundaryTest::testAtomicOperationLimitRejectsBeforeMutation](../tests/Torture/Chaos/HttpBoundaryTest.php) | PASS / PASS | 200, 400, 200 | 6, 0, 5 | 26.667, 2.093, 46.116 | 70.5, 70.5, 70.5 | 48880, 238, 2528 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testPatchReadsItsWriteWhileIndependentGetCanLag](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200, 200 | 5, 8, 5, 5 | 39.081, 51.549, 37.786, 38.897 | 70.5, 70.5, 70.5, 70.5 | 2536, 2526, 2536, 2526 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testRelationshipWriteResponseUsesPrimaryState](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200, 200 | 9, 3, 3 | 39.675, 44.661, 33.731 | 70.5, 70.5, 70.5 | 154, 212, 212 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testAtomicUsesPrimaryAndReturnsFreshResult](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 200 | 6, 5 | 44.9, 42.301 | 70.5, 70.5 | 2468, 2536 | 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 201, 404, 204, 404 | 8, 1, 4, 1 | 48.816, 32.226, 32.864, 29.754 | 70.5, 70.5, 70.5, 70.5 | 998, 259, 0, 259 | 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / APPLICATION_POLICY | 200, 200, 200 | 8, 5, 5 | 52.176, 6.872, 40.376 | 70.5, 70.5, 70.5 | 2533, 2533, 2536 | 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerProfileIncludeCriteriaAndMediaDoNotLeak](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 406, 200, 200, 200 | 5, 0, 7, 24, 7 | 16.941, 0.723, 13.817, 40.715, 13.724 | 70.5, 70.5, 70.5, 70.5, 70.5 | 601, 245, 1553, 7125, 1471 | 100, 100, 100, 100, 100 |
| [Chaos\ReplicaAndWorkerTest::testWorkerAtomicLocalIdsAreScopedToOneBatch](../tests/Torture/Chaos/ReplicaAndWorkerTest.php) | PASS / PASS | 200, 400, 200 | 3, 0, 3 | 6.633, 1.122, 3.112 | 70.5, 70.5, 70.5 | 1185, 261, 1186 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "serialization"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 16.341, 48.587, 47.342 | 70.5, 70.5, 70.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "deadlock"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 12.724, 44.339, 38.704 | 70.5, 70.5, 70.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testDriverFailureRollsBackCompleteAtomicBatch with data set "lock timeout"](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200, 200 | 10, 5, 5 | 16.101, 46.105, 45.736 | 70.5, 70.5, 70.5 | 177, 2536, 2591 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testActualPostgresLockTimeoutIsControlledAndRolledBack](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 500, 200 | 4, 7 | 105.702, 47.969 | 70.5, 70.5 | 177, 1553 | 100, 100 |
| [Chaos\TransactionFailureTest::testCrossShardAtomicIsRejectedByBundleBeforeMutation](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 409, 200, 200 | 0, 5, 1 | 3.316, 47.809, 33.167 | 70.5, 70.5, 70.5 | 336, 2536, 243 | 100, 100, 100 |
| [Chaos\TransactionFailureTest::testConcurrentRelationshipAddsPreserveBothIdentifiers](../tests/Torture/Chaos/TransactionFailureTest.php) | PASS / PASS | 200, 200, 200, 200 | 10, 10, 10, 3 | 15.91, 132.785, 116.364, 34.601 | 70.5, 18, 18, 70.5 | 139, 189, 164, 243 | 100, 100, 100, 100 |

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

