# Relationship authorization and optional error links

These are independent consumer contracts, not bundle implementations.

## Relationship authorization

[RelationshipAuthorizationTest](../tests/Acceptance/Production/RelationshipAuthorizationTest.php) verifies HTTP 403 for replacing a to-one/to-many association, adding and removing to-many targets, and clearing a to-one relationship. Related targets exist; existence does not confer permission. Persisted linkage stays unchanged. A forbidden second Atomic operation rolls back an earlier permitted write on the same connection.

The application owns role/ownership policy through ArticlePolicy. The existing production integration decorates the public RelationshipUpdater contract; no generated controller is replaced. Existing RelationshipPolicyTest retains permitted editor/admin changes and embedded relationship write checks.

[DefaultAuthorizationHookTest](../tests/Acceptance/Features/Relationships/DefaultAuthorizationHookTest.php) separately exercises the public RelationshipHook with a server-default profile. Publishing authorization decorators are inactive in this environment. All four mutations must return JSON:API 403 before persistence without the client requesting a profile. This proves a current extension seam, rather than a dedicated bundle authorization API. A client-negotiated opt-in profile alone is unsuitable for mandatory authorization.

A dedicated relationship authorization contract is still a bundle API design question. Absence of such an interface is not by itself a failing HTTP contract when a supported server-owned hook can enforce the policy. If a new authorization API is published, the isolated scenario should be adapted to exercise it while retaining the transport/state assertions.

## Optional errors[].links.type

[ErrorLinksController](../src/Controller/ErrorLinksController.php) supplies application documentation through the public JsonApiResponseFactory error builder's `withTypeLink()`. The coexistence case uses documented `ErrorBuilder::create(aboutLink: ..., typeLink: ...)` and JsonApiHttpException through the kernel error boundary. [ErrorTypeLinksTest](../tests/Acceptance/Protocol/ErrorTypeLinksTest.php) checks:

- `type` identifies a problem type, independently of an occurrence-specific `about` link;
- both links may coexist on each error;
- multiple validation errors retain their error links;
- an application that supplies no type link still returns a valid error document.

`ERROR-LINKS-TYPE` was a DESIRED_CAPABILITY, not a mandatory-member conformance violation: JSON:API makes this member optional. The updated package documents withLinks as document-level links and provides withTypeLink for error links. The consumer fixture now uses that supported API; unchanged assertions determine whether the historical gap is resolved.

The required bundle contract is an application-facing way to supply error-object links and preserve them in serialization. If the bundle deliberately separates error links from document links with a new API, update this fixture to that supported API; the desired HTTP assertions remain unchanged.

Current execution evidence is in [release-gate.md](release-gate.md) and [current-gaps.json](current-gaps.json).
