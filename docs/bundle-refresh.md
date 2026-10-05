# Bundle refresh verification

Tested installed Composer revision: `1a32d7b35ca5ce3e7c4500f52a2455655fa57c6b`.

| Full suite | Cases | Assertions | PASS | Classified failures | Unexpected / skipped |
|---|---:|---:|---:|---:|---|
| Acceptance | 588 | 5,284 | 578 | 10 | 0 / 0 |
| Torture | 62 | 4,177 | 57 | 5 | 0 / 0 |

## Remaining executable gaps

| Gap ID | Current observation | Failing cases | Category / severity |
|---|---|---:|---|
| VERSION-RESOLVER-CONTEXT | Negotiated DTO item and ordinary collection now return 500. Sparse collection and narrowly included representation tests pass; selection is no longer simply ignored. | 2 | DESIRED_CAPABILITY / P2 |
| DX-TYPED-PERSISTER-DISPATCH | Generated POST returns 500 for each of the two independently registered legacy typed persisters. | 2 | DOCUMENTATION_GAP / P1 |
| CACHE-VERSION-STRATEGY | strategy=version still produces a hash ETag; X-Resource-Version=7 is not used, and an ETag is still produced without a version. | 2 | CONFIG_IMPLEMENTATION_GAP / P2 |
| PROFILE-DEFAULT-WRITE | Per-type default audit write still leaves createdBy null without explicit profile negotiation; explicitly negotiated audit identity works. | 1 | DESIRED_CAPABILITY / P1 |
| FILTER-HANDLER-LOGICAL-COMPOSITION | search=no-match OR views=10 returns no resources instead of the matching ordinary-filter branch. | 1 | DESIRED_CAPABILITY / P1 |
| CACHE-COLLECTION-LAST-MODIFIED | collections_max_of=false still returns a collection Last-Modified header. | 1 | CONFIG_IMPLEMENTATION_GAP / P2 |
| PROFILE-AUDIT-META | Negotiated audit creation succeeds, but resource meta is absent with expose_in_meta=true. | 1 | CONFIG_IMPLEMENTATION_GAP / P2 |
| PERFORMANCE-NPLUS1 | SQL counts remain above budgets: plain collection 20/12, to-one include 42/18, to-many 28/24, nested 58/32, related collection 21/15. | 5 | PERFORMANCE_GAP / P1 |

Every failure remains a real executable assertion. Exact test/dataset names, HTTP traces and error details are in [acceptance-results.json](acceptance-results.json) and [performance-results.json](performance-results.json).

## Historical markers now passing

24 formerly open Acceptance IDs are now green:

- `ATOMIC-LID-CONFIG`
- `CACHE-SURROGATE-ROUTES`
- `CONFIG-JSON-SCHEMA`
- `CUSTOM-ACTION-QUERY-PARAMETER`
- `DATA-LAYER-CUSTOM-ATOMIC`
- `DOCS-ENDPOINT-EXAMPLES`
- `DOCS-INHERITANCE`
- `DOCS-NEGOTIATION`
- `DOCS-OPERATIONS`
- `DOCS-PAGINATION-CONFIG`
- `DOCS-WRITABLE-SCHEMA`
- `DX-PROFILE-COMMAND`
- `EXTENSIBILITY-CUSTOM-OPERATOR`
- `EXTENSIBILITY-PROFILE-DI`
- `FILTER-PUBLIC-NULL-NAMES`
- `MEDIA-CHANNEL-ROUTING`
- `PROFILE-READ-HOOK`
- `PROFILE-RELATIONSHIP-HOOK`
- `PROFILE-SOFT-BOOLEAN`
- `PROFILE-SOFT-DELETE-SEMANTICS`
- `PROFILE-SOFT-QUERY-FLAGS`
- `PROFILE-SOFT-VISIBILITY`
- `RELATIONSHIP-BUDGET-ENDPOINT`
- `WRITE-REQUEST-DTO`

`ARCHITECTURE-COMPOSITE-ID` is also resolved by the complete Torture run. Cross-connection rejection, single-connection commit/rollback and overlapping If-Match remain green.

Two consumer errors were corrected without weakening bundle contracts: selective-operation OpenAPI comparison excludes legitimate Path Item metadata such as parameters; the isolated injected profile service explicitly enables Symfony autowiring. Existing desired HTTP assertions remain. No vendor change or generic application compatibility workaround was added.

## Evidence boundary

346 public inventory entries are recorded in [feature-coverage.md](feature-coverage.md). PARTIAL/configuration-only/documentation-only entries remain distinct from verified runnable gaps; this refresh does not claim exhaustive coverage of untested variants.
