# Known bundle gaps

`docs/bundle-gaps.json` is the reviewed inventory. Tests carry `#[Group('bundle-gap')]` and `#[ExpectedBundleGap('ID')]`; they use normal assertions and are never skipped. This report validates the markers against the inventory.

Categories: `MUST_CONFORMANCE` and `SHOULD_CONFORMANCE` refer to normative JSON:API requirements; `DESIRED_CAPABILITY` is an intentional application contract; `OPTIONAL_FEATURE` is never a conformance failure merely because absent.

Baseline: bundle `5458778adc87386ffff9c07f009c25906e0971db`, PHP 8.4.26, PHPUnit 11.5.56.

## CONTENT-NEGOTIATION-001 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 3 failing / 3 cases.

- Expected: Ignore invalid Accept candidates when a valid JSON:API candidate remains.
- Current: 406 for both candidate orders and unsupported-extension fallback.
- Bundle change: Parse candidates independently in ContentNegotiationSubscriber.
- Tests:
  - [Protocol/ContentNegotiationTest::testMixedValidInvalidCandidates](../tests/Acceptance/Protocol/ContentNegotiationTest.php)

## CONTENT-NEGOTIATION-002 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 3 failing / 3 cases.

- Expected: Treat q-values as HTTP metadata; respect unacceptable q=0.
- Current: q=1 and q=0.8 return 406; q=0 already returns 406.
- Bundle change: Separate Accept quality values from JSON:API media parameters.
- Tests:
  - [Protocol/ContentNegotiationTest::testQualityValues](../tests/Acceptance/Protocol/ContentNegotiationTest.php)

## ATOMIC-001 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 2 failing / 2 cases.

- Expected: Infer add target from data.type without ref/href. Domain-invalid title-only articles reach 422 validation.
- Current: 400 missing target before domain validation.
- Bundle change: AtomicValidator target inference.
- Tests:
  - [Atomic/AtomicCreateTest::testCanonicalAddWithoutRefOrHref](../tests/Acceptance/Atomic/AtomicCreateTest.php)
  - [Atomic/AtomicCreateTest::testTitleOnlyCanonicalAddReachesDomainValidation](../tests/Acceptance/Atomic/AtomicCreateTest.php)

## ATOMIC-002 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 1 failing / 1 cases.

- Expected: Infer update target from data.type and data.id.
- Current: 400 missing target.
- Bundle change: AtomicValidator target inference.
- Tests:
  - [Atomic/AtomicUpdateTest::testUpdateTargetFromDataWithoutRefOrHref](../tests/Acceptance/Atomic/AtomicUpdateTest.php)

## ATOMIC-003 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 1 failing / 1 cases.

- Expected: Reject ref with both id and lid.
- Current: 200 accepts the id and ignores lid.
- Bundle change: AtomicRequestParser exclusive identifier validation.
- Tests:
  - [Atomic/AtomicValidationTest::testRefCannotContainBothIdAndLid](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-004 — Atomic Operations

**MUST_CONFORMANCE · P0**. Observed: 2 failing / 2 cases.

- Expected: Return generated IDs and resolve lids after persisting generated-ID resources.
- Current: 400 Unable to resolve resource identifier for persisted model, before operation flush.
- Bundle change: AddHandler/OperationDispatcher flush and lid resolution ordering.
- Tests:
  - [Atomic/AtomicCreateTest::testCreateUsingHrefAndGeneratedId](../tests/Acceptance/Atomic/AtomicCreateTest.php)
  - [Atomic/AtomicLidTest::testLocalIdsAcrossCreateAndRelationshipOperations](../tests/Acceptance/Atomic/AtomicLidTest.php)

## ATOMIC-005 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Accept same-origin absolute and path-relative URI references.
- Current: 400 rejects absolute URLs and references without leading slash.
- Bundle change: AtomicValidator URI-reference resolution and same-origin checks.
- Tests:
  - [Atomic/AtomicValidationTest::testSameOriginAbsoluteHref](../tests/Acceptance/Atomic/AtomicValidationTest.php)
  - [Atomic/AtomicValidationTest::testRelativeUriReference](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-006 — Atomic Operations

**DESIRED_CAPABILITY · P0**. Observed: 1 failing / 1 cases.

- Expected: Reject href with unrecognized trailing path segments.
- Current: 200 ignores trailing path segments and removes the resource.
- Bundle change: AtomicValidator must consume and validate the entire href.
- Tests:
  - [Atomic/AtomicValidationTest::testInvalidHrefBundleGap](../tests/Acceptance/Atomic/AtomicValidationTest.php) (#1)

## ATOMIC-007 — Atomic Operations

**MUST_CONFORMANCE · P1**. Observed: 1 failing / 1 cases.

- Expected: Serialize empty atomic result entries as JSON objects.
- Current: 200 emits [] entries instead of {}.
- Bundle change: ResultBuilder empty object serialization.
- Tests:
  - [Atomic/AtomicValidationTest::testEmptyAtomicResultsMustBeObjects](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-008 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Return each operation representation at its position in the batch.
- Current: Earlier update result reflects the final mutable entity state.
- Bundle change: Snapshot operation outcomes before processing later mutations.
- Tests:
  - [Atomic/AtomicTransactionalityTest::testSuccessfulBatchOrderAndMixedEmptyResults](../tests/Acceptance/Atomic/AtomicTransactionalityTest.php)

## ATOMIC-009 — Atomic Operations

**DESIRED_CAPABILITY · P0**. Observed: 1 failing / 1 cases.

- Expected: Apply read-only operation policy to atomic resource updates.
- Current: 422 serializer rejection instead of policy 403; operation authorization is bypassed.
- Bundle change: Atomic execution must enforce ResourceOperation restrictions.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicReadonlyResourceCannotBeWritten](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-010 — Atomic Operations

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Apply the configured client-generated ID policy to atomic creates.
- Current: 500 attempts to assign a string to a generated integer ID.
- Bundle change: Atomic AddHandler must apply WriteConfig client-ID policy.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicClientIdPolicyMatchesOrdinaryCreates](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-011 — errors

**MUST_CONFORMANCE · P1**. Observed: 2 failing / 2 cases.

- Expected: Use /atomic:operations/N/data/... pointers in atomic errors.
- Current: Errors contain ordinary /data/... pointers.
- Bundle change: Rebase write/validation error pointers onto their atomic operation.
- Tests:
  - [Atomic/AtomicValidationTest::testAtomicUnknownAttributeIsRejected](../tests/Acceptance/Atomic/AtomicValidationTest.php)
  - [Atomic/AtomicValidationTest::testAtomicFailurePointerIdentifiesOperationAndExternalField](../tests/Acceptance/Atomic/AtomicValidationTest.php)

## ATOMIC-012 — content negotiation

**MUST_CONFORMANCE · P0**. Observed: 1 failing / 1 cases.

- Expected: Recognize the enabled Atomic extension with strict global negotiation.
- Current: 415 rejects all extensions before AtomicController. A public channel isolates Atomic execution tests.
- Bundle change: Connect enabled extensions to global media-type negotiation.
- Tests:
  - [Atomic/AtomicNegotiationTest::testEnabledAtomicExtensionWithStrictGlobalNegotiation](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)

## ATOMIC-013 — content negotiation

**MUST_CONFORMANCE · P1**. Observed: 2 failing / 2 cases.

- Expected: Atomic negotiation rejects incorrect base media types and unsupported Content-Type parameters.
- Current: AtomicController accepts text/plain with atomic ext and JSON:API atomic with charset.
- Bundle change: Use a shared structured media-type parser for Atomic requests.
- Tests:
  - [Atomic/AtomicNegotiationTest::testWrongBaseMediaTypeWithAtomicExtIsRejected](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)
  - [Atomic/AtomicNegotiationTest::testAtomicMediaParameterRejected](../tests/Acceptance/Atomic/AtomicNegotiationTest.php)

## CACHE-001 — cache/preconditions

**DESIRED_CAPABILITY · P0**. Observed: 4 failing / 4 cases.

- Expected: Evaluate write preconditions before mutation; matching GET ETag permits a write.
- Current: Valid If-Match gets 412; stale/missing conditions return 412/428 after changes have already persisted.
- Bundle change: CachePreconditionsSubscriber currently runs at kernel.response; move write checks before persistence.
- Tests:
  - [Cache/WritePreconditionsTest::testMatchingIfMatchAllowsUpdate](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testStaleIfMatchRejectsWithoutChangingState](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testStaleIfMatchDoesNotDelete](../tests/Acceptance/Cache/WritePreconditionsTest.php)
  - [Cache/WritePreconditionsTest::testRequiredPrecondition](../tests/Acceptance/Cache/WritePreconditionsTest.php)

## CACHE-002 — cache/preconditions

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Resolve Last-Modified from the configured updatedAt field.
- Current: Header uses current wall-clock time instead of the entity timestamp.
- Bundle change: LastModifiedResolver must use resource metadata/configuration.
- Tests:
  - [Cache/ConditionalRequestsTest::testLastModifiedUsesConfiguredEntityField](../tests/Acceptance/Cache/ConditionalRequestsTest.php)

## DOCTRINE-001 — Doctrine

**DESIRED_CAPABILITY · P1**. Observed: 3 failing / 3 cases.

- Expected: Map unique violations to JSON:API 409 and roll back the transaction.
- Current: 500 escapes from transaction/flush; rollback checks themselves pass.
- Bundle change: Map database exceptions raised inside DoctrineTransactionManager and Atomic dispatch.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testUniqueConstraintOnCreate](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Doctrine/DoctrineConstraintsTest::testUniqueConstraintOnUpdate](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Atomic/AtomicTransactionalityTest::testEarlierCreateIsRolledBackBundleGap](../tests/Acceptance/Atomic/AtomicTransactionalityTest.php) (#1)

## DOCTRINE-002 — Doctrine

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Report FK restriction as JSON:API 422 and retain the parent.
- Current: 500 from an unmapped Doctrine FK exception.
- Bundle change: DatabaseErrorMapper integration at transaction flush/delete.
- Tests:
  - [Resource/ResourceDeleteTest::testForeignKeyRestrictionIsAnErrorDocument](../tests/Acceptance/Resource/ResourceDeleteTest.php)

## RELATIONSHIP-001 — errors

**DESIRED_CAPABILITY · P1**. Observed: 3 failing / 3 cases.

- Expected: Point missing related identifiers at the public linkage id member.
- Current: 404 pointer omits /data/id or refers to the relationship rather than its identifier.
- Bundle change: RelationshipResolver and relationship endpoint error pointers.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testMissingRelationshipReference](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Resource/ResourceUpdateTest::testUnknownRelatedResourceInPatch](../tests/Acceptance/Resource/ResourceUpdateTest.php)
  - [Relationships/ToOneRelationshipTest::testInvalidLinkageBundleGap](../tests/Acceptance/Relationships/ToOneRelationshipTest.php) (#1)

## RELATIONSHIP-002 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Use consistent 409 for linkage resource-type conflicts in ordinary create/update requests.
- Current: Relationship endpoints use 409, resource writes use 422.
- Bundle change: Unify resource-write and relationship endpoint type-conflict validation.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testWrongRelationshipType](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)
  - [Resource/ResourceUpdateTest::testWrongRelatedTypeInPatch](../tests/Acceptance/Resource/ResourceUpdateTest.php)

## RELATIONSHIP-003 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Reject unknown relationship writes with 400.
- Current: 201 silently discards unknown relationship names.
- Bundle change: InputDocumentValidator relationship whitelist.
- Tests:
  - [Doctrine/DoctrineConstraintsTest::testUnknownRelationship](../tests/Acceptance/Doctrine/DoctrineConstraintsTest.php)

## RELATIONSHIP-004 — relationships

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Reject null on required author with a client validation error.
- Current: 500 DB not-null violation when clearing required relationship.
- Bundle change: Validate required relationship metadata before persistence.
- Tests:
  - [Relationships/ToOneRelationshipTest::testInvalidLinkageBundleGap](../tests/Acceptance/Relationships/ToOneRelationshipTest.php) (#3)

## RELATIONSHIP-005 — relationships

**DESIRED_CAPABILITY · P2**. Observed: 1 failing / 1 cases.

- Expected: Provide a related link in the linkage document as an application navigation contract.
- Current: The resource relationship object has related, but its standalone linkage document omits it.
- Bundle change: Linkage document navigation links.
- Tests:
  - [Relationships/ToOneRelationshipTest::testReadLinkageAndRelatedResource](../tests/Acceptance/Relationships/ToOneRelationshipTest.php)

## HTTP-001 — HEAD/OPTIONS

**DESIRED_CAPABILITY · P1**. Observed: 4 failing / 4 cases.

- Expected: HEAD sends GET-compatible ETag and an empty body.
- Current: Bodies are empty, but HEAD ETags differ from GET for all route categories.
- Bundle change: Generate validators from GET representation before stripping HEAD body.
- Tests:
  - [Protocol/HeadTest::testHeadHasGetHeadersAndNoBody](../tests/Acceptance/Protocol/HeadTest.php)

## HTTP-002 — HEAD/OPTIONS

**DESIRED_CAPABILITY · P1**. Observed: 6 failing / 6 cases.

- Expected: Generated OPTIONS routes execute and report cardinality-aware Allow methods.
- Current: 500: OptionsController has constructor arguments but is not registered in the container.
- Bundle change: Register OptionsController and exclude POST/DELETE for to-one relationships.
- Tests:
  - [Protocol/OptionsTest::testAllowHeader](../tests/Acceptance/Protocol/OptionsTest.php)
  - [Protocol/OperationRestrictionsTest::testReadonlyOptions](../tests/Acceptance/Protocol/OperationRestrictionsTest.php)

## FILTER-001 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Support registered neq and documented ne not-equals operators.
- Current: neq is rejected by parser; ne reaches an unknown compiler operator and returns 500.
- Bundle change: Align FilterParser, operator registry and filter whitelist.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (neq public operator, ne)

## FILTER-002 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Compile supported between filters and reject extra operands.
- Current: 500: Between AST is unsupported by DoctrineFilterCompiler.
- Bundle change: Handle Between AST and require exactly two operands.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (between)
  - [Query/QueryValidationTest::testBetweenRejectsExtraOperand](../tests/Acceptance/Query/QueryValidationTest.php)

## FILTER-003 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Compile isnull true/false through configured nullable field aliases.
- Current: 500: NullCheck AST is unsupported by DoctrineFilterCompiler.
- Bundle change: Handle NullCheck AST and resolve attribute aliases.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (null, not null)

## FILTER-004 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Execute PostgreSQL ilike with the app's installed DBAL 4.
- Current: 500: PostgreSQL platform no longer has getName().
- Bundle change: Update Doctrine ILikeFunction platform detection for DBAL 4.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (ilike)

## FILTER-005 — filtering

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Define empty IN as no matches and empty NOT IN as all matches.
- Current: 500 for empty string operands on the integer field.
- Bundle change: Validate/coerce set operands; compile deliberate empty-set semantics.
- Tests:
  - [Query/QueryValidationTest::testEmptyInMatchesNothing](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testEmptyNotInMatchesEverything](../tests/Acceptance/Query/QueryValidationTest.php)

## ALIAS-001 — aliases

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Translate external attribute aliases for filter and sort.
- Current: 500: published-at is emitted as a DQL path instead of publishedAt.
- Bundle change: ResourceMetadata.resolveFieldPath must map attribute names as well as relationships.
- Tests:
  - [Query/FilteringTest::testSupportedOperatorsBundleGap](../tests/Acceptance/Query/FilteringTest.php) (datetime alias)
  - [Query/SortingTest::testNullableAliasSort](../tests/Acceptance/Query/SortingTest.php)

## QUERY-001 — query parsing

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Reject malformed page lists and report source.parameter=page.
- Current: page[]=1 succeeds; scalar page error uses a different source parameter.
- Bundle change: QueryParser pagination member shape validation.
- Tests:
  - [Query/QueryValidationTest::testMalformedPageListRejected](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testScalarPageRejected](../tests/Acceptance/Query/QueryValidationTest.php)

## QUERY-002 — query parsing

**DESIRED_CAPABILITY · P1**. Observed: 3 failing / 3 cases.

- Expected: Reject invalid operand objects/types and excessive filter depth at HTTP boundary.
- Current: Object operand and deep groups succeed; invalid integer operand leaks a DB 500.
- Bundle change: Bound and type-check parsed filter operands before Doctrine.
- Tests:
  - [Query/QueryValidationTest::testQueryBoundaryBundleGap](../tests/Acceptance/Query/QueryValidationTest.php) (wrong operator shape)
  - [Query/QueryValidationTest::testWrongIntegerOperandProducesClientError](../tests/Acceptance/Query/QueryValidationTest.php)
  - [Query/QueryValidationTest::testExcessiveFilterDepthIsRejected](../tests/Acceptance/Query/QueryValidationTest.php)

## ERROR-001 — errors

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Provide a stable nonempty title for query errors.
- Current: Unknown filter field response has code/status/source but no title.
- Bundle change: Complete ErrorMapper title mapping for query whitelist errors.
- Tests:
  - [Query/QueryValidationTest::testQueryBoundaryBundleGap](../tests/Acceptance/Query/QueryValidationTest.php) (unknown filter field)

## ERROR-002 — errors

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Report malformed to-one resource linkage as JSON:API 400 with public data pointer.
- Current: 500 for array linkage in a resource create document.
- Bundle change: Validate resource relationship cardinality/shape before resolving.
- Tests:
  - [Protocol/ErrorDocumentTest::testMalformedRelationshipDataPointsToData](../tests/Acceptance/Protocol/ErrorDocumentTest.php)

## INCLUDE-001 — compound documents

**MUST_CONFORMANCE · P1**. Observed: 1 failing / 1 cases.

- Expected: When include is requested and the nullable relationship is empty, emit included: [].
- Current: The included member is omitted.
- Bundle change: DocumentBuilder requested-but-empty include handling.
- Tests:
  - [Query/IncludeTest::testNullableInclude](../tests/Acceptance/Query/IncludeTest.php)

## WRITE-001 — writes

**MUST_CONFORMANCE · P1**. Observed: 1 failing / 1 cases.

- Expected: Accept empty attributes object in a partial PATCH.
- Current: 400: associative decoding conflates {} with [].
- Bundle change: Preserve JSON object/array distinction in request validation.
- Tests:
  - [Resource/ResourceUpdateTest::testEmptyAttributeObjectIsValid](../tests/Acceptance/Resource/ResourceUpdateTest.php)

## PROFILE-001 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Apply per-type default relationship count profile.
- Current: No count metadata for tags without Accept profile; negotiated profile works.
- Bundle change: Per-type ProfileContext activation in DocumentBuilder.
- Tests:
  - [Profiles/ProfileTest::testProfileEnabledByDefaultForTags](../tests/Acceptance/Profiles/ProfileTest.php)

## PROFILE-002 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Negotiated soft-delete profile excludes archived categories on collection/item reads.
- Current: Archived categories remain visible.
- Bundle change: Integrate soft-delete query hooks with Doctrine collection and item reads.
- Tests:
  - [Profiles/ProfileTest::testSoftDeleteProfileExcludesArchivedCategory](../tests/Acceptance/Profiles/ProfileTest.php)

## PROFILE-003 — profiles

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Negotiated audit profile advances and persists updatedAt on writes.
- Current: Timestamp remains the seeded value; write hooks are not integrated.
- Bundle change: Integrate audit WriteHook into resource processors/controllers.
- Tests:
  - [Profiles/ProfileTest::testAuditTrailProfileUpdatesTimestamp](../tests/Acceptance/Profiles/ProfileTest.php)

## SORT-001 — sorting

**DESIRED_CAPABILITY · P1**. Observed: 1 failing / 1 cases.

- Expected: Implicit ID tiebreaker keeps tied title pagination deterministic after updates.
- Current: Tied rows reverse after an unrelated row update.
- Bundle change: Append a stable identifier ordering when absent from client sorting.
- Tests:
  - [Query/SortingTest::testImplicitIdTiebreakerSurvivesUpdates](../tests/Acceptance/Query/SortingTest.php)

## UUID-001 — Doctrine UUID identifiers

**DESIRED_CAPABILITY · P1**. Observed: 2 failing / 2 cases.

- Expected: Client-supplied UUID strings create resources with a typed Symfony Uuid identifier through ordinary POST and Atomic add.
- Current: Both requests return 500 because client ID assignment passes a string to setId(Uuid). Constructor-generated UUID objects and UUID string identifiers work.
- Bundle change: Doctrine processors must convert client identifiers using mapped Doctrine/Serializer types before PropertyAccessor assignment.
- Tests:
  - [Doctrine/UuidIdentifierTest::testClientAssignedDoctrineUuidObject](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)
  - [Doctrine/UuidIdentifierTest::testAtomicClientAssignedDoctrineUuidObject](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)

## UUID-002 — Doctrine UUID identifiers

**DESIRED_CAPABILITY · P2**. Observed: 1 failing / 1 cases.

- Expected: A malformed identifier in a UUID resource URL produces a JSON:API 400 client error.
- Current: GET /api/newsletters/not-a-uuid returns 500 when Doctrine UuidType rejects the identifier. A valid nonexistent UUID correctly returns 404.
- Bundle change: Validate mapped UUID identifiers at the HTTP boundary or translate Doctrine UID conversion errors into JSON:API client errors.
- Tests:
  - [Doctrine/UuidIdentifierTest::testMalformedUuidDoesNotLeakServerError](../tests/Acceptance/Doctrine/UuidIdentifierTest.php)
