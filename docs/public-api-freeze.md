# Public API review

The consumer records bounded executable contracts in [feature coverage](feature-coverage.md) and [feature-review.json](feature-review.json). [Public API decisions](public-api-decisions.json) records the resolved extension choices on the tested bundle revision. Current failing contracts belong in [current gaps](current-gaps.json), not the historical audit.

Typed repositories, persisters and relationship services dispatch through `supports(type)` and documented tags. [TypedRelationshipTest](../tests/Acceptance/Features/DataLayer/TypedRelationshipTest.php) proves two source types, all endpoint reader/updater operations, automatic and explicit registration, isolated mutations, pagination and reader fallback. Representation preloading, application authorization and durable transaction semantics remain distinct capabilities.

Custom profiles and hook DTOs, query-plan scope forwarding, safe write models, projections and response builders have independent consumer assertions. Green means the linked bounded contract, not every possible composition. Removed inactive configuration is not advertised as a feature; its earlier audit is in [history](history/configuration-dx-audit.md).

A GO from the external gate proves executed consumer behavior on one platform. It does not publish the bundle or substitute for a published RC/final package, immutable evidence tags or the release owner's API/versioning decision. Cross-connection Atomic rejects before mutation; the package does not promise distributed transactions. External event delivery remains application-owned.
