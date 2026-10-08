# Known bundle gaps

`docs/bundle-gaps.json` is the reviewed inventory. Only active failing cases carry `#[Group('bundle-gap')]` and `#[ExpectedBundleGap('ID')]`; resolved tests keep their assertions and historical target references; they use normal assertions and are never skipped. This report validates the markers against the inventory.

Categories: `MUST_CONFORMANCE` and `SHOULD_CONFORMANCE` refer to normative JSON:API requirements; `DESIRED_CAPABILITY` is an intentional application contract; `OPTIONAL_FEATURE` is never a conformance failure merely because absent. `APPLICATION_POLICY` belongs to the application; `INFRASTRUCTURE_LIMIT` belongs to the runtime/database/distributed system; `DOCUMENTATION_GAP` describes documentation/contract drift or discoverability. `DX_GAP` describes public integration ergonomics/tooling. `CONFIG_IMPLEMENTATION_GAP` identifies accepted configuration with no corresponding runtime implementation.

Baseline: bundle `96a1530f3155ddf001b7d1e48fd33e375c382d85`, PHP 8.2.34, PHPUnit 11.5.57.

## CONTENT-NEGOTIATION-001 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 3 cases.

- Expected: Ignore invalid Accept candidates when a valid JSON:API candidate remains.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 406 for both candidate orders and unsupported-extension fallback.
- Bundle change: Parse candidates independently in ContentNegotiationSubscriber.
- Tests:
  - [Protocol/ContentNegotiationTest::testMixedValidInvalidCandidates](../tests/Acceptance/Protocol/ContentNegotiationTest.php)

## CONTENT-NEGOTIATION-002 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 3 cases.

- Expected: Treat q-values as HTTP metadata; respect unacceptable q=0.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: q=1 and q=0.8 return 406; q=0 already returns 406.
- Bundle change: Separate Accept quality values from JSON:API media parameters.
- Tests:
  - [Protocol/ContentNegotiationTest::testQualityValues](../tests/Acceptance/Protocol/ContentNegotiationTest.php)

## ATOMIC-001 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 2 cases.

- Expected: Infer add target from data.type without ref/href. Domain-invalid title-only articles reach 422 validation.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400 missing target before domain validation.
- Bundle change: AtomicValidator target inference.
- Tests:
  - [Atomic/AtomicCreateTest::testCanonicalAddWithoutRefOrHref](../tests/Acceptance/Atomic/AtomicCreateTest.php)
  - [Atomic/AtomicCreateTest::testTitleOnlyCanonicalAddReachesDomainValidation](../tests/Acceptance/Atomic/AtomicCreateTest.php)

## ATOMIC-002 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 1 cases.

- Expected: Infer update target from data.type and data.id.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400 missing target.
- Bundle change: AtomicValidator target inference.
- Tests:
  - [Atomic/AtomicUpdateTest::testUpdateTargetFromDataWithoutRefOrHref](../tests/Acceptance/Atomic/AtomicUpdateTest.php)

## ATOMIC-003 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 1 cases.

- Expected: Reject ref with both id and lid.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 200 accepts the id and ignores lid.
- Bundle change: AtomicRequestParser exclusive identifier validation.
- Tests:
  - [Atomic/AtomicValidationTest::testRefCannotContainBothIdAndLid](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-004 — Atomic Operations

**MUST_CONFORMANCE · P0**. Observed: 0 failing / 2 cases.

- Expected: Return generated IDs and resolve lids after persisting generated-ID resources.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400 Unable to resolve resource identifier for persisted model, before operation flush.
- Bundle change: AddHandler/OperationDispatcher flush and lid resolution ordering.
- Tests:
  - [Atomic/AtomicCreateTest::testCreateUsingHrefAndGeneratedId](../tests/Acceptance/Atomic/AtomicCreateTest.php)
  - [Atomic/AtomicLidTest::testLocalIdsAcrossCreateAndRelationshipOperations](../tests/Acceptance/Atomic/AtomicLidTest.php)

## ATOMIC-005 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Accept same-origin absolute and path-relative URI references.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400 rejects absolute URLs and references without leading slash.
- Bundle change: AtomicValidator URI-reference resolution and same-origin checks.
- Tests:
  - [Atomic/AtomicValidationTest::testSameOriginAbsoluteHref](../tests/Acceptance/Atomic/AtomicValidationTest.php)
  - [Atomic/AtomicValidationTest::testRelativeUriReference](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-006 — Atomic Operations

**DESIRED_CAPABILITY · P0**. Observed: 0 failing / 1 cases.

- Expected: Reject href with unrecognized trailing path segments.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 200 ignores trailing path segments and removes the resource.
- Bundle change: AtomicValidator must consume and validate the entire href.
- Tests:
  - [Atomic/AtomicValidationTest::testInvalidHrefBundleGap](../tests/Acceptance/Atomic/AtomicValidationTest.php) (#1)

## ATOMIC-007 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 1 cases.

- Expected: Serialize empty atomic result entries as JSON objects.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 200 emits [] entries instead of {}.
- Bundle change: ResultBuilder empty object serialization.
- Tests:
  - [Atomic/AtomicValidationTest::testEmptyAtomicResultsMustBeObjects](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-008 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Return each operation representation at its position in the batch.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Earlier update result reflects the final mutable entity state.
- Bundle change: Snapshot operation outcomes before processing later mutations.
- Tests:
  - [Atomic/AtomicTransactionalityTest::testSuccessfulBatchOrderAndMixedEmptyResults](../tests/Acceptance/Atomic/AtomicTransactionalityTest.php)

## ATOMIC-009 — Atomic Operations

**DESIRED_CAPABILITY · P0**. Observed: 0 failing / 1 cases.

- Expected: Apply read-only operation policy to atomic resource updates.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 422 serializer rejection instead of policy 403; operation authorization is bypassed.
- Bundle change: Atomic execution must enforce ResourceOperation restrictions.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicReadonlyResourceCannotBeWritten](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-010 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Apply the configured client-generated ID policy to atomic creates.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 attempts to assign a string to a generated integer ID.
- Bundle change: Atomic AddHandler must apply WriteConfig client-ID policy.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicClientIdPolicyMatchesOrdinaryCreates](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-011 — errors

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 2 cases.

- Expected: Use /atomic:operations/N/data/... pointers in atomic errors.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Errors contain ordinary /data/... pointers.
- Bundle change: Rebase write/validation error pointers onto their atomic operation.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicUnknownAttributeIsRejected](../tests/Acceptance/Atomic/AtomicValidationTest.php)
  - [Atomic/AtomicValidationTest::testAtomicFailurePointerIdentifiesOperationAndExternalField](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-012 — content negotiation

**MUST_CONFORMANCE · P0**. Observed: 0 failing / 1 cases.

- Expected: Recognize the enabled Atomic extension with strict global negotiation.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 415 rejects all extensions before AtomicController. A public channel isolates Atomic execution tests.
- Bundle change: Connect enabled extensions to global media-type negotiation.
- Tests:
  - [Atomic/AtomicNegotiationTest::testEnabledAtomicExtensionWithStrictGlobalNegotiation](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)

## ATOMIC-013 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 2 cases.

- Expected: Atomic negotiation rejects incorrect base media types and unsupported Content-Type parameters.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: AtomicController accepts text/plain with atomic ext and JSON:API atomic with charset.
- Bundle change: Use a shared structured media-type parser for Atomic requests.
- Tests:
  - [Atomic/AtomicNegotiationTest::testWrongBaseMediaTypeWithAtomicExtIsRejected](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)
  - [Atomic/AtomicNegotiationTest::testAtomicMediaParameterRejected](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)

## CACHE-001 — cache/preconditions

**DESIRED_CAPABILITY · P0**. Observed: 0 failing / 4 cases.

- Expected: Evaluate write preconditions before mutation; matching GET ETag permits a write.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Valid If-Match gets 412; stale/missing conditions return 412/428 after changes have already persisted.
- Bundle change: CachePreconditionsSubscriber currently runs at kernel.response; move write checks before persistence.
- Tests:
  - [Cache/WritePreconditionsTest::testMatchingIfMatchAllowsUpdate](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testStaleIfMatchRejectsWithoutChangingState](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testStaleIfMatchDoesNotDelete](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testRequiredPrecondition](../tests/Acceptance/Cache/WritePreconditionsTest.php)

## CACHE-002 — cache/preconditions

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Resolve Last-Modified from the configured updatedAt field.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Header uses current wall-clock time instead of the entity timestamp.
- Bundle change: LastModifiedResolver must use resource metadata/configuration.
- Tests:
  - [Cache/ConditionalRequestsTest::testLastModifiedUsesConfiguredEntityField](../tests/Acceptance/Cache/ConditionalRequestsTest.php)

## DOCTRINE-001 — Doctrine

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 3 cases.

- Expected: Map unique violations to JSON:API 409 and roll back the transaction.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 escapes from transaction/flush; rollback checks themselves pass.
- Bundle change: Map database exceptions raised inside DoctrineTransactionManager and Atomic dispatch.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testUniqueConstraintOnCreate](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Doctrine/DoctrineConstraintsTest::testUniqueConstraintOnUpdate](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Atomic/AtomicTransactionalityTest::testEarlierCreateIsRolledBackBundleGap](../tests/Acceptance/Atomic/AtomicTransactionalityTest.php) (#1)

## DOCTRINE-002 — Doctrine

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Report FK restriction as JSON:API 422 and retain the parent.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 from an unmapped Doctrine FK exception.
- Bundle change: DatabaseErrorMapper integration at transaction flush/delete.
- Tests:
  - [Resource/ResourceDeleteTest::testForeignKeyRestrictionIsAnErrorDocument](../tests/Acceptance/Resource/ResourceDeleteTest.php)

## RELATIONSHIP-001 — errors

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 3 cases.

- Expected: Point missing related identifiers at the public linkage id member.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 404 pointer omits /data/id or refers to the relationship rather than its identifier.
- Bundle change: RelationshipResolver and relationship endpoint error pointers.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testMissingRelationshipReference](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Resource/ResourceUpdateTest::testUnknownRelatedResourceInPatch](../tests/Acceptance/Resource/ResourceUpdateTest.php)
  - [Relationships/ToOneRelationshipTest::testInvalidLinkageBundleGap](../tests/Acceptance/Relationships/ToOneRelationshipTest.php) (#1)

## RELATIONSHIP-002 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Use consistent 409 for linkage resource-type conflicts in ordinary create/update requests.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Relationship endpoints use 409, resource writes use 422.
- Bundle change: Unify resource-write and relationship endpoint type-conflict validation.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testWrongRelationshipType](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Resource/ResourceUpdateTest::testWrongRelatedTypeInPatch](../tests/Acceptance/Resource/ResourceUpdateTest.php)

## RELATIONSHIP-003 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Reject unknown relationship writes with 400.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 201 silently discards unknown relationship names.
- Bundle change: InputDocumentValidator relationship whitelist.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testUnknownRelationship](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)

## RELATIONSHIP-004 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Reject null on required author with a client validation error.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 DB not-null violation when clearing required relationship.
- Bundle change: Validate required relationship metadata before persistence.
- Tests:
  - [Relationships/ToOneRelationshipTest::testInvalidLinkageBundleGap](../tests/Acceptance/Relationships/ToOneRelationshipTest.php) (#3)

## RELATIONSHIP-005 — relationships

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: Provide a related link in the linkage document as an application navigation contract.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: The resource relationship object has related, but its standalone linkage document omits it.
- Bundle change: Linkage document navigation links.
- Tests:
  - [Relationships/ToOneRelationshipTest::testReadLinkageAndRelatedResource](../tests/Acceptance/Relationships/ToOneRelationshipTest.php)

## HTTP-001 — HEAD/OPTIONS

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 4 cases.

- Expected: HEAD sends GET-compatible ETag and an empty body.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Bodies are empty, but HEAD ETags differ from GET for all route categories.
- Bundle change: Generate validators from GET representation before stripping HEAD body.
- Tests:
  - [Protocol/HeadTest::testHeadHasGetHeadersAndNoBody](../tests/Acceptance/Protocol/HeadTest.php)

## HTTP-002 — HEAD/OPTIONS

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 6 cases.

- Expected: Generated OPTIONS routes execute and report cardinality-aware Allow methods.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500: OptionsController has constructor arguments but is not registered in the container.
- Bundle change: Register OptionsController and exclude POST/DELETE for to-one relationships.
- Tests:
  - [Protocol/OptionsTest::testAllowHeader](../tests/Acceptance/Protocol/OptionsTest.php)
  - [Protocol/OperationRestrictionsTest::testReadonlyOptions](../tests/Acceptance/Protocol/OperationRestrictionsTest.php)

## FILTER-001 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Support registered neq and documented ne not-equals operators.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: neq is rejected by parser; ne reaches an unknown compiler operator and returns 500.
- Bundle change: Align FilterParser, operator registry and filter whitelist.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (neq public operator, ne)

## FILTER-002 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Compile supported between filters and reject extra operands.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500: Between AST is unsupported by DoctrineFilterCompiler.
- Bundle change: Handle Between AST and require exactly two operands.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (between)
  - [Query/QueryValidationTest::testBetweenRejectsExtraOperand](../tests/Acceptance/Query/QueryValidationTest.php)

## FILTER-003 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Compile isnull true/false through configured nullable field aliases.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500: NullCheck AST is unsupported by DoctrineFilterCompiler.
- Bundle change: Handle NullCheck AST and resolve attribute aliases.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (null, not null)

## FILTER-004 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Execute PostgreSQL ilike with the app's installed DBAL 4.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500: PostgreSQL platform no longer has getName().
- Bundle change: Update Doctrine ILikeFunction platform detection for DBAL 4.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (ilike)

## FILTER-005 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Define empty IN as no matches and empty NOT IN as all matches.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 for empty string operands on the integer field.
- Bundle change: Validate/coerce set operands; compile deliberate empty-set semantics.
- Tests:
  - [Query/QueryValidationTest::testEmptyInMatchesNothing](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testEmptyNotInMatchesEverything](../tests/Acceptance/Query/QueryValidationTest.php)

## ALIAS-001 — aliases

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Translate external attribute aliases for filter and sort.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500: published-at is emitted as a DQL path instead of publishedAt.
- Bundle change: ResourceMetadata.resolveFieldPath must map attribute names as well as relationships.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (datetime alias)
  - [Query/SortingTest::testNullableAliasSort](../tests/Acceptance/Query/SortingTest.php)

## QUERY-001 — query parsing

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Reject malformed page lists and report source.parameter=page.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: page[]=1 succeeds; scalar page error uses a different source parameter.
- Bundle change: QueryParser pagination member shape validation.
- Tests:
  - [Query/QueryValidationTest::testMalformedPageListRejected](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testScalarPageRejected](../tests/Acceptance/Query/QueryValidationTest.php)

## QUERY-002 — query parsing

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 3 cases.

- Expected: Reject invalid operand objects/types and excessive filter depth at HTTP boundary.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Object operand and deep groups succeed; invalid integer operand leaks a DB 500.
- Bundle change: Bound and type-check parsed filter operands before Doctrine.
- Tests:
  - [Query/QueryValidationTest::testQueryBoundaryBundleGap](../tests/Acceptance/Query/QueryValidationTest.php) (wrong operator shape)
  - [Query/QueryValidationTest::testWrongIntegerOperandProducesClientError](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testExcessiveFilterDepthIsRejected](../tests/Acceptance/Query/QueryValidationTest.php)

## ERROR-001 — errors

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Provide a stable nonempty title for query errors.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Unknown filter field response has code/status/source but no title.
- Bundle change: Complete ErrorMapper title mapping for query whitelist errors.
- Tests:
  - [Query/QueryValidationTest::testQueryBoundaryBundleGap](../tests/Acceptance/Query/QueryValidationTest.php) (unknown filter field)

## ERROR-002 — errors

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Report malformed to-one resource linkage as JSON:API 400 with public data pointer.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 500 for array linkage in a resource create document.
- Bundle change: Validate resource relationship cardinality/shape before resolving.
- Tests:
  - [Protocol/ErrorDocumentTest::testMalformedRelationshipDataPointsToData](../tests/Acceptance/Protocol/ErrorDocumentTest.php)

## INCLUDE-001 — compound documents

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 1 cases.

- Expected: When include is requested and the nullable relationship is empty, emit included: [].
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: The included member is omitted.
- Bundle change: DocumentBuilder requested-but-empty include handling.
- Tests:
  - [Query/IncludeTest::testNullableInclude](../tests/Acceptance/Query/IncludeTest.php)

## WRITE-001 — writes

**MUST_CONFORMANCE · P1**. Observed: 0 failing / 1 cases.

- Expected: Accept empty attributes object in a partial PATCH.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400: associative decoding conflates {} with [].
- Bundle change: Preserve JSON object/array distinction in request validation.
- Tests:
  - [Resource/ResourceUpdateTest::testEmptyAttributeObjectIsValid](../tests/Acceptance/Resource/ResourceUpdateTest.php)

## PROFILE-001 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Apply per-type default relationship count profile.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: No count metadata for tags without Accept profile; negotiated profile works.
- Bundle change: Per-type ProfileContext activation in DocumentBuilder.
- Tests:
  - [Profiles/ProfileTest::testProfileEnabledByDefaultForTags](../tests/Acceptance/Profiles/ProfileTest.php)

## PROFILE-002 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Negotiated soft-delete profile excludes archived categories on collection/item reads.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Archived categories remain visible.
- Bundle change: Integrate soft-delete query hooks with Doctrine collection and item reads.
- Tests:
  - [Profiles/ProfileTest::testSoftDeleteProfileExcludesArchivedCategory](../tests/Acceptance/Profiles/ProfileTest.php)

## PROFILE-003 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Negotiated audit profile advances and persists updatedAt on writes.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Timestamp remains the seeded value; write hooks are not integrated.
- Bundle change: Integrate audit WriteHook into resource processors/controllers.
- Tests:
  - [Profiles/ProfileTest::testAuditTrailProfileUpdatesTimestamp](../tests/Acceptance/Profiles/ProfileTest.php)

## SORT-001 — sorting

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Implicit ID tiebreaker keeps tied title pagination deterministic after updates.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Tied rows reverse after an unrelated row update.
- Bundle change: Append a stable identifier ordering when absent from client sorting.
- Tests:
  - [Query/SortingTest::testImplicitIdTiebreakerSurvivesUpdates](../tests/Acceptance/Query/SortingTest.php)

## UUID-001 — Doctrine UUID identifiers

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Client-supplied UUID strings create resources with a typed Symfony Uuid identifier through ordinary POST and Atomic add.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Both requests return 500 because client ID assignment passes a string to setId(Uuid). Constructor-generated UUID objects and UUID string identifiers work.
- Bundle change: Doctrine processors must convert client identifiers using mapped Doctrine/Serializer types before PropertyAccessor assignment.
- Tests:
  - [Doctrine/UuidIdentifierTest::testClientAssignedDoctrineUuidObject](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)
  - [Doctrine/UuidIdentifierTest::testAtomicClientAssignedDoctrineUuidObject](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)

## UUID-002 — Doctrine UUID identifiers

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: A malformed identifier in a UUID resource URL produces a JSON:API 400 client error.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: GET /api/newsletters/not-a-uuid returns 500 when Doctrine UuidType rejects the identifier. A valid nonexistent UUID correctly returns 404.
- Bundle change: Validate mapped UUID identifiers at the HTTP boundary or translate Doctrine UID conversion errors into JSON:API client errors.
- Tests:
  - [Doctrine/UuidIdentifierTest::testMalformedUuidDoesNotLeakServerError](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)

## EXTENSIBILITY-PROFILE-DI — Production profile dependency injection

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: A tagged public ProfileInterface service can use required constructor injection and the HTTP application still boots.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Container validation reports the registered profile as missing when it has a required constructor dependency; HTTP cannot start.
- Bundle change: Support dependency-injected profile services during validation; obtain static descriptors without constructing unconfigured application services.
- Tests:
  - [Production/ExtensibilityTest::testPublicProfileSupportsConstructorInjectedUserContext](../tests/Acceptance/Production/ExtensibilityTest.php)
- Responsibility: Symfony service construction is part of the public extension integration. Application policies need injected context; they must not be forced into service-location or mutable global state.

## QUERY-SCOPE-GRAPH — Production ownership scope across graph reads

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 3 cases.

- Expected: Application-owned Article scope applies before pagination to INDEX, related collections, includes and relationship linkage; foreign objects and identifiers are not exposed.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: ResourceRepository decoration scopes INDEX/SHOW and DTO projections, but related collections, includes and linkage bypass that scope and expose Grace articles to editor A.
- Bundle change: Provide one mandatory resource query/visibility extension used for root queries, relationship pagination, identifier discovery and includes, independent of client profile negotiation.
- Tests:
  - [Production/QueryScopeTest::testRelatedCollectionCannotLeakForeignArticles](../tests/Acceptance/Production/QueryScopeTest.php)
  - [Production/QueryScopeTest::testIncludesCannotLeakForeignArticles](../tests/Acceptance/Production/QueryScopeTest.php)
  - [Production/QueryScopeTest::testRelationshipLinkageCannotLeakForeignIdentifiers](../tests/Acceptance/Production/QueryScopeTest.php)
- Responsibility: Choosing visibility is application policy. Consistently invoking a public query-scope extension across every generated graph read is bundle infrastructure; post-filtering hydrated pages or replacing include/linkage engines would hide the missing seam.

## CUSTOM-ACTION-READ-MODEL — Custom-route-only aggregate read model

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: A custom handler can serialize a registered non-Doctrine aggregate resource with operations=[] using its custom GET route, without requiring fictitious generated CRUD routes.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: CustomRouteResult::resource attempts to generate jsonapi.author-publishing-statistics.show although SHOW is disabled; the aggregate endpoint returns 500.
- Bundle change: Support canonical/self link metadata for custom-route-only resources or safely serialize resources without a generated SHOW route.
- Tests:
  - [Production/ReadModelTest::testAggregateResourceIsIndependentOfDoctrineWriteModel](../tests/Acceptance/Production/ReadModelTest.php)
- Responsibility: The aggregate SQL and authorization are application-owned. Resource serialization and link generation must support the public custom-route/read-model contract without forcing unrelated persistence routes.

## WRITE-MODEL-SERIALIZER-METADATA — Symfony external serializer mapping

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 5 cases.

- Expected: The bundle honors effective Symfony Serializer metadata, including configured environment-specific YAML write groups; original wide-surface test contracts remain executable while publishing uses attribute-only safe groups.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Symfony debug:serializer reports articles:write for the configured test fields, but generated JSON:API writes reject views/status/published-at as extra attributes (422). Five original desired round-trip/workflow assertions fail without being changed.
- Bundle change: Use effective Symfony serializer metadata consistently when determining allowed input fields, including external mapping paths and inherited metadata.
- Tests:
  - [Doctrine/DoctrineTypesTest::testWriteTypesRoundTrip](../tests/Acceptance/Doctrine/DoctrineTypesTest.php)
  - [Resource/ResourceCreateTest::testAllWritableAttributesAndRelationships](../tests/Acceptance/Resource/ResourceCreateTest.php)
  - [Resource/ResourceUpdateTest::testAttributesAndRelationshipsTogether](../tests/Acceptance/Resource/ResourceUpdateTest.php)
  - [Smoke/RealWorldWorkflowTest::testPublishingWorkflow](../tests/Acceptance/Smoke/RealWorldWorkflowTest.php)
  - [Mapping/FieldAliasesTest::testReadAndWriteAlias](../tests/Acceptance/Mapping/FieldAliasesTest.php)
- Responsibility: Resolving effective Symfony Serializer input metadata is generic transport integration. Consumers should not need to duplicate serializer mapping in bundle internals or replace generic deserialization. Production attribute groups already enforce the safe surface correctly.

## ATOMIC-VALIDATION-BOUNDARY — Existing mixed-manager validation expectation

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: Reject a batch spanning independent PostgreSQL and MySQL connections with 409 unsupported-transaction-boundary before either operation mutates storage, including when a later operation would fail validation.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 409 unsupported-transaction-boundary; both resources remain unchanged. The user confirmed this single-connection contract; earlier 422 business-validation precedence is no longer required.
- Bundle change: No change required on the tested revision; keep early rejection and single-connection rollback guarantees.
- Tests:
  - [Atomic/AtomicTransactionalityTest::testMixedManagerBatchIsRejectedBeforeAnyMutation](../tests/Acceptance/Atomic/AtomicTransactionalityTest.php)
- Responsibility: The bundle must detect incompatible transaction boundaries before executing any operation; distributed transactions are outside its contract.

## EXTENSIBILITY-CUSTOM-OPERATOR — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 3 cases.

- Expected: Registered custom filter operators execute through HTTP parser and Doctrine compilation.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: 400 Unsupported operator starts_with before registered operator invocation.
- Bundle change: Public operator registry must participate in parser validation; application must not replace generic parser.
- Tests:
  - [Features/Filtering/InheritanceAndExtensionsTest::testPublicQueryExtensions](../tests/Acceptance/Features/Filtering/InheritanceAndExtensionsTest.php) (custom operator, logical custom operator, bound SQL literal)
- Responsibility: Public operator registry must participate in parser validation; application must not replace generic parser.

## DOCS-OPERATIONS — Public feature conformance

**DOCUMENTATION_GAP · P1**. Observed: 0 failing / 3 cases.

- Expected: OpenAPI advertises only enabled resource operations.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Read-only audit-logs advertises POST.
- Bundle change: OpenAPI generator must honor ResourceOperation metadata.
- Tests:
  - [Features/Docs/OpenApiTest::testGeneratedResourceOperations](../tests/Acceptance/Features/Docs/OpenApiTest.php)
  - [Features/Docs/OpenApiTest::testCustomOnlyResourceDoesNotAdvertiseCrud](../tests/Acceptance/Features/Docs/OpenApiTest.php)
  - [Features/Docs/OpenApiTest::testSelectiveOperationsMatchActualCollectionAndItemRoutes](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: OpenAPI generator must honor ResourceOperation metadata.

## DOCS-NEGOTIATION — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 5 cases.

- Expected: Documentation endpoints accept their native response media types with strict negotiation enabled.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: OpenAPI returns 406 for Accept application/json; UI returns 406 for text/html.
- Bundle change: Bundle-owned documentation routes must integrate with negotiation policy without consumer route replacements.
- Tests:
  - [Features/Docs/OpenApiTest::testDocumentationAcceptsItsNativeMediaType](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Bundle-owned documentation routes must integrate with negotiation policy without consumer route replacements.

## CONFIG-JSON-SCHEMA — Public feature conformance

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: Enabled JSON Schema generator exposes its configured HTTP route.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Default enabled schema endpoint returns 404; schema configuration has no routing implementation.
- Bundle change: Public configuration must have a working external contract or be removed/deprecated clearly.
- Tests:
  - [Features/Docs/OpenApiTest::testEnabledJsonSchemaRouteExists](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Public configuration must have a working external contract or be removed/deprecated clearly.

## CUSTOM-ACTION-QUERY-PARAMETER — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: Custom handlers receive application query parameters alongside JSON:API criteria.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Query parser rejects title before CustomRouteContext::getQueryParam can consume it.
- Bundle change: Expose a per-route query allowlist or documented parsing seam.
- Tests:
  - [Features/CustomRoutes/HandlerContractTest::testApplicationQueryParameterReachesCustomHandler](../tests/Acceptance/Features/CustomRoutes/HandlerContractTest.php)
- Responsibility: Expose a per-route query allowlist or documented parsing seam.

## PROFILE-READ-HOOK — Public feature conformance

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Negotiated public ReadHook restricts collection before pagination.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: QueryHook caps page size, but ReadHook custom condition is ignored: views 10/20 instead of 100/110.
- Bundle change: Generated repositories must invoke public read hooks with negotiated context.
- Tests:
  - [Features/Profiles/PublicHooksTest::testDocumentQueryAndReadHooksCompose](../tests/Acceptance/Features/Profiles/PublicHooksTest.php)
- Responsibility: Generated repositories must invoke public read hooks with negotiated context.

## PROFILE-RELATIONSHIP-HOOK — Public feature conformance

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Negotiated RelationshipHook can reject associations before mutation.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: PATCH succeeds 200 and changes author despite hook throwing ForbiddenException.
- Bundle change: All generated relationship writes must invoke public relationship hooks before persistence.
- Tests:
  - [Features/Profiles/PublicHooksTest::testRelationshipHookRejectsBeforePersistence](../tests/Acceptance/Features/Profiles/PublicHooksTest.php)
- Responsibility: All generated relationship writes must invoke public relationship hooks before persistence.

## WRITE-REQUEST-DTO — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: Operation-specific writeRequests input class validates incoming attributes.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Short title receives 201; configured DTO Length(min:20) ignored.
- Bundle change: Make public operation input metadata participate in normal validation/denormalization; document supported keys.
- Tests:
  - [Features/Mapping/PublicInputAndVersionTest::testOperationSpecificInputClassIsValidated](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php)
- Responsibility: Make public operation input metadata participate in normal validation/denormalization; document supported keys.

## VERSION-RESOLVER-CONTEXT — Public feature conformance

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 5 cases.

- Expected: VersionResolver receives negotiated profile and selects the configured DTO mapping.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Negotiated alternate representation returns JSON:API 500 through SHOW, INDEX and related collection; sparse INDEX and included-resource cases pass.
- Bundle change: Carry request ProfileContext into public representation-definition resolution.
- Tests:
  - [Features/Mapping/PublicInputAndVersionTest::testNegotiatedVersionChangesRepresentation](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php)
  - [Features/Mapping/PublicInputAndVersionTest::testVersionSelectionComposesAcrossCollectionsIncludesAndFields](../tests/Acceptance/Features/Mapping/PublicInputAndVersionTest.php)
- Responsibility: Carry request ProfileContext into public representation-definition resolution.

## DX-PROFILE-COMMAND — Public feature conformance

**DX_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: Documented jsonapi:validate-profiles command is registered.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Symfony Console has no jsonapi namespace despite command class present.
- Bundle change: Register the public command and document availability/dependencies.
- Tests:
  - [Features/Configuration/PublicConfigurationTest::testProfileValidationCommandRunsFromConsumerContainer](../tests/Acceptance/Features/Configuration/PublicConfigurationTest.php)
- Responsibility: Register the public command and document availability/dependencies.

## FILTER-PUBLIC-NULL-NAMES — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 2 cases.

- Expected: Public FilterableField null/nnull operators execute as null checks.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Both names return400 unsupported operator; isnull alias works.
- Bundle change: Align parser operator vocabulary with public metadata and docs.
- Tests:
  - [Features/Filtering/NativeOperatorsTest::testDocumentedNullOperatorNames](../tests/Acceptance/Features/Filtering/NativeOperatorsTest.php)
- Responsibility: Align parser operator vocabulary with public metadata and docs.

## RELATIONSHIP-BUDGET-ENDPOINT — Public feature conformance

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Relationship identifier budget applies to standalone linkage endpoints.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Root/include reject oversized tags, standalone linkage returns200 with all identifiers.
- Bundle change: Enforce the same budget before legacy relationship identifier reads.
- Tests:
  - [Features/Relationships/IdentifierBudgetTest::testOversizedRelationshipIsRejectedWithoutTruncation](../tests/Acceptance/Features/Relationships/IdentifierBudgetTest.php) (linkage endpoint)
- Responsibility: Enforce the same budget before legacy relationship identifier reads.

## CACHE-SURROGATE-ROUTES — Public feature conformance

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 2 cases.

- Expected: Generated resource/collection routes emit configured surrogate keys.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: No X-Cache-Tags header; builder recognizes legacy generic names rather than generated resource-specific names.
- Bundle change: Resolve resource identity from public route metadata consistently for all generated routes.
- Tests:
  - [Features/Cache/HeaderConfigurationTest::testConfiguredCacheHeadersAndSurrogateResource](../tests/Acceptance/Features/Cache/HeaderConfigurationTest.php)
  - [Features/Cache/HeaderConfigurationTest::testStrongCollectionValidatorAndCollectionKey](../tests/Acceptance/Features/Cache/HeaderConfigurationTest.php)
- Responsibility: Resolve resource identity from public route metadata consistently for all generated routes.

## DOCS-INHERITANCE — Public feature conformance

**DOCUMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: OpenAPI expands inherited filter/sort whitelists including aliases and exclusions.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Spec advertises relationship root author rather than executable author.name/email fields.
- Bundle change: Document effective whitelist rather than raw inheritance declarations.
- Tests:
  - [Features/Docs/OpenApiTest::testInheritedWhitelistIsExpandedInDocumentation](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Document effective whitelist rather than raw inheritance declarations.

## DOCS-WRITABLE-SCHEMA — Public feature conformance

**DOCUMENTATION_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: OpenAPI schema distinguishes server-owned attributes from writable inputs.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: createdAt/updatedAt lack readOnly despite HTTP rejecting client writes.
- Bundle change: Generate operation input schemas or readOnly/writeOnly from effective serializer metadata.
- Tests:
  - [Features/Docs/OpenApiTest::testReadOnlyAttributesAreNotAdvertisedAsWritable](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Generate operation input schemas or readOnly/writeOnly from effective serializer metadata.

## DOCS-PAGINATION-CONFIG — Public feature conformance

**DOCUMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: OpenAPI pagination defaults/limits equal effective application configuration.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Spec default20/max100 while runtime configuration default5/max20.
- Bundle change: Read effective public pagination/limits configuration during generation.
- Tests:
  - [Features/Docs/OpenApiTest::testPaginationDocumentationUsesEffectiveConfiguration](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Read effective public pagination/limits configuration during generation.

## ATOMIC-LID-CONFIG — Atomic configuration

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 1 cases.

- Expected: Setting lid.accept_in_resource_and_identifier=false rejects resource and identifier lids.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Atomic add accepts data.lid and creates the Author with200 despite the disabled policy.
- Bundle change: Apply lid policy consistently to data resources as well as operation refs.
- Tests:
  - [Features/Atomic/ConfigurationMatrixTest::testAtomicConfigurationGuards](../tests/Acceptance/Features/Atomic/ConfigurationMatrixTest.php) (lid)
- Responsibility: Enforcing a public Atomic protocol configuration is transport infrastructure.

## PROFILE-SOFT-VISIBILITY — Public configuration interoperability

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 2 cases.

- Expected: Soft-delete default_visibility include/only controls visible rows.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Both include and only return the default three undeleted categories.
- Bundle change: Wire public visibility configuration into the negotiated query hook.
- Tests:
  - [Features/Profiles/SoftDeleteConfigurationTest::testConfiguredVisibility](../tests/Acceptance/Features/Profiles/SoftDeleteConfigurationTest.php) (include, only)
- Responsibility: Wire public visibility configuration into the negotiated query hook.

## PROFILE-SOFT-DELETE-SEMANTICS — Public configuration interoperability

**CONFIG_IMPLEMENTATION_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: delete_semantics=soft retains the row and records deletion state.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: DELETE204 physically removes the category despite configured soft semantics.
- Bundle change: Implement the declared generic deletion policy or explicitly remove/deprecate the misleading option.
- Tests:
  - [Features/Profiles/SoftDeleteConfigurationTest::testDeleteSemanticsPersistExpectedState](../tests/Acceptance/Features/Profiles/SoftDeleteConfigurationTest.php) (soft)
- Responsibility: Implement the declared generic deletion policy or explicitly remove/deprecate the misleading option.

## PROFILE-SOFT-QUERY-FLAGS — Public configuration interoperability

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 2 cases.

- Expected: Configured soft-delete flags are consumed as profile query controls.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: includeArchived returns400 filter-not-allowed before the profile can consume it.
- Bundle change: Connect public flag names with query parsing and whitelist handling.
- Tests:
  - [Features/Profiles/SoftDeleteConfigurationTest::testConfiguredQueryFlagIsConsumedBeforeWhitelist](../tests/Acceptance/Features/Profiles/SoftDeleteConfigurationTest.php)
- Responsibility: Connect public flag names with query parsing and whitelist handling.

## PROFILE-SOFT-BOOLEAN — Public configuration interoperability

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: strategy=boolean excludes true deletion markers and retains false markers.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Boolean strategy still uses IS NULL; both nonnullable false/true records disappear.
- Bundle change: Compile configured strategy using boolean predicates and validate compatible metadata.
- Tests:
  - [Features/Profiles/BooleanSoftDeleteTest::testBooleanStrategyExcludesOnlyMarkedRows](../tests/Acceptance/Features/Profiles/BooleanSoftDeleteTest.php)
- Responsibility: Compile configured strategy using boolean predicates and validate compatible metadata.

## MEDIA-CHANNEL-ROUTING — Public configuration interoperability

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 2 cases.

- Expected: Route-name and attribute channels select configured request/response policy.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Path channel works; route/attribute POST application/json returns415 using default policy.
- Bundle change: Resolve routing/MediaChannel metadata before selecting scoped negotiation policy.
- Tests:
  - [Features/Protocol/MediaChannelsTest::testPublicChannelScopeAndResponse](../tests/Acceptance/Features/Protocol/MediaChannelsTest.php) (route, attribute)
- Responsibility: Resolve routing/MediaChannel metadata before selecting scoped negotiation policy.

## DOCS-ENDPOINT-EXAMPLES — OpenAPI endpoint attributes

**DOCUMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: Public OpenApiEndpoint examples/OpenApiExample values appear in the generated HTTP specification.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Summary, parameters, schemaRef, headers and security are emitted; examples silently disappear.
- Bundle change: Serialize configured named examples at the appropriate OpenAPI request/response media location.
- Tests:
  - [Features/Docs/OpenApiTest::testPublicEndpointExamplesArePresentInSpec](../tests/Acceptance/Features/Docs/OpenApiTest.php)
- Responsibility: Interpreting public documentation attributes is the bundle generator responsibility.

## DX-TYPED-PERSISTER-DISPATCH — typed data layer

**DOCUMENTATION_GAP · P1**. Observed: 0 failing / 2 cases.

- Expected: Documented jsonapi.persister tagged TypedResourcePersister implementations handle generated create routes independently for multiple resource types.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Typed repository reads succeed, but both typed create routes return JSON:API 500; current ResourceProcessor dispatch ignores the documented legacy persister registration.
- Bundle change: Provide and document an active typed write contract/registration seam, or deprecate obsolete persister/tag examples with an executable migration example.
- Tests:
  - [Features/DataLayer/TypedProviderTest::testDocumentedTypedPersisterRegistrationHandlesGeneratedWrites](../tests/Acceptance/Features/DataLayer/TypedProviderTest.php)
- Responsibility: The bundle advertises the public legacy contract and tag; independent consumers need a supported typed write registration path or an explicit documented migration to ResourceProcessor. Reimplementing dispatch in the application would hide this mismatch.

## CACHE-VERSION-STRATEGY — HTTP caching

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 2 cases.

- Expected: cache.etag.strategy=version uses X-Resource-Version supplied by the application; no version produces no ETag.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: The configured strategy still generates body-hash ETags, including when no version header is present.
- Bundle change: Wire the version strategy through the public configuration and document the response-version header contract.
- Tests:
  - [Features/Cache/VersionStrategyTest::testConfiguredVersionStrategyUsesApplicationVersion](../tests/Acceptance/Features/Cache/VersionStrategyTest.php)
- Responsibility: Selecting the configured ETag generator belongs to bundle configuration, not application alias replacement.

## PROFILE-DEFAULT-WRITE — profile lifecycle

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Per-type default profiles execute write hooks without an explicit Accept profile; application identity is recorded on create.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Explicitly negotiated builtin audit records createdBy/updatedBy, but per_type default alone creates the memo with createdBy=null.
- Bundle change: Resolve default and per-type profiles before write-hook execution on generated mutations.
- Tests:
  - [Features/Profiles/AuditIdentityTest::testPerTypeDefaultProfileAppliesToWriteHooksWithoutExplicitNegotiation](../tests/Acceptance/Features/Profiles/AuditIdentityTest.php)
- Responsibility: Default-profile selection must apply consistently to read and write lifecycle hooks. Applications should not require clients to request server audit policy.

## FILTER-HANDLER-LOGICAL-COMPOSITION — custom filter composition

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 4 cases.

- Expected: Custom predicates retain their AST position in AND, both OR orders and nested alternatives, with bound parameters.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: search=No such term OR views=10 returns zero resources rather than article-1; the handler adds its predicate as a global AND condition.
- Bundle change: Expose an expression/predicate custom-filter contract that composes at the original logical AST position, including bound parameters and error mapping.
- Tests:
  - [Features/Filtering/SearchCompositionTest::testHandlerInsideOrPreservesAlternativeNormalPredicate](../tests/Acceptance/Features/Filtering/SearchCompositionTest.php)
- Responsibility: The public imperative FilterHandlerInterface cannot naturally contribute a predicate at its AST position. Rebuilding logical query compilation in the application would replace generic bundle behavior.

## DATA-LAYER-CUSTOM-ATOMIC — custom data provider and Atomic

**CONFIG_IMPLEMENTATION_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: A resource-only Atomic batch uses the configured custom processor/transaction manager; a business failure rolls back the earlier mutation and returns 422.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Atomic controller returns500 before executing the batch: custom-provider services reference the nonexistent AlexFigures\Symfony\Contract\Data\NullRelationshipUpdater class.
- Bundle change: Provide valid custom-provider fallback services and avoid mandatory initialization of unused relationship capabilities for standard Atomic resource mutations.
- Tests:
  - [Features/DataLayer/CustomProviderTest::testAtomicBusinessFailureRollsBackEarlierCustomProviderMutation](../tests/Acceptance/Features/DataLayer/CustomProviderTest.php)
- Responsibility: A supported custom provider must boot the resource-only Atomic dispatcher without requiring an application dummy implementation for an unused optional relationship write service.

## CACHE-COLLECTION-LAST-MODIFIED — Last-Modified configuration

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: collections_max_of=false disables automatically computed collection Last-Modified, while item validators remain available.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: collections_max_of is accepted but only appears in configuration; runtime always computes a maximum.
- Bundle change: Honor the documented configuration or explicitly remove/deprecate the unsupported option with a migration contract.
- Tests:
  - [Features/Cache/DisabledCollectionLastModifiedTest::testDisablingCollectionMaximumDoesNotSynthesizeCollectionValidator](../tests/Acceptance/Features/Cache/DisabledCollectionLastModifiedTest.php)
- Responsibility: This is bundle-owned public configuration; implementing an application substitute would hide its missing behavior.

## PROFILE-AUDIT-META — Audit profile metadata

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: expose_in_meta=true exposes server-owned audit information in negotiated resource metadata.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: The public AuditTrailDocumentHook is a placeholder; the option has no observable effect.
- Bundle change: Honor the documented configuration or explicitly remove/deprecate the unsupported option with a migration contract.
- Tests:
  - [Features/Profiles/AuditIdentityTest::testConfiguredAuditMetaIsExposedOnNegotiatedRepresentation](../tests/Acceptance/Features/Profiles/AuditIdentityTest.php)
- Responsibility: This is bundle-owned public configuration; implementing an application substitute would hide its missing behavior.

## RESOURCE-ROUTE-PREFIX — Resource mapping

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: JsonApiResource.routePrefix overrides the global prefix for generated routes and representation links.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: GET /reference/routed-articles/1 returns 404 despite resource routePrefix=/reference.
- Bundle change: Honor the resource-specific route prefix consistently in generated routes and links.
- Tests:
  - [Features/Mapping/ResourceOptionsTest::testResourceRoutePrefixOverridesGlobalPrefixAndLinks](../tests/Acceptance/Features/Mapping/ResourceOptionsTest.php)

## PROFILE-AUDIT-ATTRIBUTE-FIELDS — Reviewed public feature contract

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 2 cases.

- Expected: Auditable renamed timestamp/user fields are honored on CREATE and UPDATE.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Renamed insertedBy remains null on CREATE; equivalent configuration mapping passes.
- Bundle change: Auditable renamed timestamp/user fields are honored on CREATE and UPDATE.
- Tests:
  - [Features/Profiles/AuditFieldNamesTest::testCustomAuditFieldNamesTrackCreateAndUpdate](../tests/Acceptance/Features/Profiles/AuditFieldNamesTest.php)

## CONFIG-HEAD-DISABLED — Reviewed public feature contract

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: head_enabled=false disables HEAD and OPTIONS no longer advertises it.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: HEAD returns 200 despite explicit false.
- Bundle change: head_enabled=false disables HEAD and OPTIONS no longer advertises it.
- Tests:
  - [Features/Protocol/DisabledHeadTest::testDisabledHeadIsUnavailableAndOptionsAgrees](../tests/Acceptance/Features/Protocol/DisabledHeadTest.php)

## PROFILE-REL-COUNT-CONFIG — Reviewed public feature contract

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: rel_counts.relationship_meta_key names count metadata consistently.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: cardinality key absent; default count key still used.
- Bundle change: rel_counts.relationship_meta_key names count metadata consistently.
- Tests:
  - [Features/Profiles/NegotiationOptionsTest::testCustomRelationshipCountKeyIsUsed](../tests/Acceptance/Features/Profiles/NegotiationOptionsTest.php)

## PROFILE-REL-COUNT-RELATED-POLICY — Reviewed public feature contract

**CONFIG_IMPLEMENTATION_GAP · P2**. Observed: 0 failing / 2 cases.

- Expected: compute_in_related_endpoints=false suppresses relationship count computation/exposure on related endpoints.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: count is still present when false; true case passes.
- Bundle change: compute_in_related_endpoints=false suppresses relationship count computation/exposure on related endpoints.
- Tests:
  - [Features/Profiles/RelatedCountOptionsTest::testRelatedEndpointCountPolicy](../tests/Acceptance/Features/Profiles/RelatedCountOptionsTest.php)

## RESOURCE-RELATIONSHIP-POLICIES — Reviewed public feature contract

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Resource-level relationshipPolicies apply when no per-relationship policy is specified.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Resource map declares VERIFY but effective relationship metadata stays REFERENCE.
- Bundle change: Resource-level relationshipPolicies apply when no per-relationship policy is specified.
- Tests:
  - [Features/Mapping/RegistryContractTest::testResourceLevelRelationshipPolicyAppliesToUnspecifiedRelationship](../tests/Acceptance/Features/Mapping/RegistryContractTest.php)

## RESOURCE-REGISTRY-PROJECTION-COLLISION — Reviewed public feature contract

**DESIRED_CAPABILITY · P1**. Observed: 0 failing / 1 cases.

- Expected: Registering a DTO projection must not replace the primary entity resource in getByClass.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: FeatureArticle class resolves to feature-custom-summaries instead of feature-articles.
- Bundle change: Registering a DTO projection must not replace the primary entity resource in getByClass.
- Tests:
  - [Features/Mapping/RegistryContractTest::testProjectionDoesNotReplacePrimaryEntityClassRegistration](../tests/Acceptance/Features/Mapping/RegistryContractTest.php)

## DOCS-EXPOSE-ID-CONTRACT — Reviewed public feature contract

**DOCUMENTATION_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: Read resource OpenAPI keeps id required/non-null even if exposeId=false; transport identity remains valid.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: HTTP returns valid id but schema omits id from required.
- Bundle change: Read resource OpenAPI keeps id required/non-null even if exposeId=false; transport identity remains valid.
- Tests:
  - [Features/Mapping/ResourceOptionsTest::testExposeIdFalseDoesNotMakeProtocolIdentityOptionalInDocumentation](../tests/Acceptance/Features/Mapping/ResourceOptionsTest.php)

## RESOURCE-TAG-DISCOVERY — Reviewed public feature contract

**DX_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: jsonapi.resource service registration composes with directory discovery.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Tagged resource outside discovery paths has no generated route: 404.
- Bundle change: jsonapi.resource service registration composes with directory discovery.
- Tests:
  - [Features/Mapping/ResourceOptionsTest::testExplicitResourceServiceTagWorksOutsideDiscoveryPaths](../tests/Acceptance/Features/Mapping/ResourceOptionsTest.php)

## MEDIA-DEFAULT-POLICY — Media type configuration

**CONFIG_IMPLEMENTATION_GAP · P1**. Observed: 0 failing / 4 cases.

- Expected: Configured default request/response media policies apply consistently to generated reads and writes; explicit negotiation remains authoritative.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Configured application/json request policy is rejected with 415 on generated writes; response defaults stay application/vnd.api+json instead of configured application/json.
- Bundle change: Resolve request and response media policy uniformly for generated resource routes, including default and negotiated cases.
- Tests:
  - [Features/Protocol/DefaultMediaPolicyTest::testConfiguredDefaultMediaRequestAndResponsePolicy](../tests/Acceptance/Features/Protocol/DefaultMediaPolicyTest.php)
  - [Features/Protocol/DefaultMediaPolicyTest::testConfiguredRequestPolicyAppliesToGeneratedWrites](../tests/Acceptance/Features/Protocol/DefaultMediaPolicyTest.php)

## DOCS-METADATA-CONTRACT — Public metadata interface

**DX_GAP · P1**. Observed: 0 failing / 1 cases.

- Expected: Default ResourceMetadata implementation fulfills the published ResourceMetadataInterface contract.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: The concrete default ResourceMetadata does not implement the public interface promised by its API documentation.
- Bundle change: Make the default metadata implementation conform to its published interface or correct/remove the claim and provide a supported implementation seam.
- Tests:
  - [Features/Mapping/RegistryContractTest::testDocumentedDefaultMetadataImplementsPublicMetadataContract](../tests/Acceptance/Features/Mapping/RegistryContractTest.php)

## DX-PUBLIC-SIGNATURE-INTERNAL-DTO — Public extension signatures

**DX_GAP · P1**. Observed: 0 failing / 3 cases.

- Expected: Consumer-facing extension contracts use public stable DTOs or public supported replacements.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Batch-reader and custom-route registry signatures expose types marked @internal.
- Bundle change: Promote stable DTOs or replace the public signatures before 1.0; avoid requiring application code to depend on @internal types.
- Tests:
  - [Features/Configuration/PublicSignatureTest::testPublicExtensionSignatureUsesSupportedDto](../tests/Acceptance/Features/Configuration/PublicSignatureTest.php)

## PROFILE-SOFT-DELETE-ACTOR-META — Soft-delete public attribute

**DOCUMENTATION_GAP · P2**. Observed: 0 failing / 1 cases.

- Expected: Negotiated soft-delete resource metadata exposes an application-supplied deletion actor using SoftDeletable.deletedByField.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: Document hook is a documented placeholder; actor metadata is absent.
- Bundle change: Honor the documented actor metadata mapping or remove the unsupported promise/attribute option before 1.0. Actor assignment remains application policy.
- Tests:
  - [Features/Profiles/SoftDeleteActorTest::testConfiguredActorFieldAppearsInNegotiatedSoftDeleteMetadata](../tests/Acceptance/Features/Profiles/SoftDeleteActorTest.php)

## ERROR-LINKS-TYPE — JSON:API optional error links

**DESIRED_CAPABILITY · P2**. Observed: 0 failing / 3 cases.

- Expected: Application-supplied problem type is serialized as errors[].links.type; optional about identifies the occurrence, both coexist, and omission remains valid. The same contract applies to every validation error.
- Current on tested revision: PASS; historical gap resolved for all covered cases.
- Historical baseline: ResponseFactory error()->withLinks stores type/about at document level; errors[].links.type is absent for single and multiple errors.
- Bundle change: Provide a public error-link input supporting type and about and preserve them per error through response serialization. An explicitly separate API for document-level links is fine; do not require application-built JSON responses. Adapt this consumer fixture if the supported API deliberately changes.
- Tests:
  - [Protocol/ErrorTypeLinksTest::testApplicationErrorTypeLinkIsSerializedOnEachError](../tests/Acceptance/Protocol/ErrorTypeLinksTest.php)
- Responsibility: The application chooses documentation URIs; the bundle owns JSON:API error object construction and serialization. The optional member is not required on every error, but an explicitly supplied error link must be representable through the public API.
