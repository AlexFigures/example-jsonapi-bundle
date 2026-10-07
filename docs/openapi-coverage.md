# OpenAPI public contract coverage

The spec is fetched through `GET /_jsonapi/openapi.json`, not through the generator service.
The installed dependency is pinned to `e0b6f935fe5bbc1ae577a4ef2a6c414eb393ca6a`.
`Features/Docs/OpenApiTest`, `RedocTest`, `DisabledDocumentationTest` and `DisabledUiTest`
provide the independent consumer evidence. Native media negotiation is tested separately;
`Accept: */*` allows inspection of the specification without hiding the native-media failure.

| Actual HTTP capability | Generated OpenAPI capability | Match / mismatch | Contract |
|---|---|---|---|
| Article CRUD and relationship routes | Paths and JSON:API schemas present | Match for normal CRUD | Generated resource operations |
| AuditLog read-only HTTP routes | Only enabled read operations advertised | Match; historical DOCS-OPERATIONS resolved | Resource operations |
| Custom-only publishing statistics, operations=[] | Disabled generated CRUD paths absent | Match; historical DOCS-OPERATIONS resolved | Custom-only resource paths |
| FeatureRecord SHOW/CREATE/UPDATE with disabled INDEX/DELETE | Only enabled HTTP methods advertised; legal Path Item parameters excluded from method comparison | Match; historical DOCS-OPERATIONS resolved | Selective resource paths |
| Disabled/configured Atomic endpoint | Spec follows enabled flag and configured path | Match | Atomic endpoint matrix |
| POST article publish handler | Handler route present automatically | Match | Custom handler routes |
| Enum, nullable datetime, boolean and numeric attributes | Types/formats/enum emitted | Match | Primitive schema test |
| Production timestamps, views, status, publication timestamp reject input; allowed fields and author association succeed | Server-owned attributes marked readOnly | Match; historical DOCS-WRITABLE-SCHEMA resolved | Canonical EffectiveWriteSchemaTest |
| Inherited author.name/email and aliased tags.name work | Inherited effective whitelist present | Match; historical DOCS-INHERITANCE resolved | Inherited whitelist test |
| Runtime default page5, maximum20 | Configured default5 and maximum20 emitted | Match; historical DOCS-PAGINATION-CONFIG resolved | Effective pagination test |
| Custom starts_with is registered and executes through HTTP | Spec advertises starts_with | Match; historical EXTENSIBILITY-CUSTOM-OPERATOR resolved | Operator HTTP test |
| Controller summary, description, operationId, tags, parameters, request body, schemaRef, response header, security and deprecated | Emitted with correct explicit contentType | Match | Controller attributes test |
| OpenApiExample configured on public endpoint | Configured example emitted | Match; historical DOCS-ENDPOINT-EXAMPLES resolved | Example test |
| Default strict negotiation | Native OpenAPI/JSON/Schema/HTML media accepted | Match; historical DOCS-NEGOTIATION resolved | Native-media test |
| Configured OpenAPI title/version/servers | Values emitted | Match | Configured identity test |
| Swagger/Redoc themes and independent UI/spec disable | Shell and route enablement follow configuration | Match | UI matrix |
| JSON Schema generator enabled with configured route | Endpoint200, draft2020-12, local $defs refs, independent of disabled OpenAPI | Match; historical CONFIG-JSON-SCHEMA resolved | Schema endpoint test |

Remaining schema details (UUID format, separate operation input schemas, required validator groups,
all relationship cardinalities and aggregate-sort discoverability under reject policy) retain PARTIAL
coverage in the feature inventory. Passing endpoint/schema assertions do not imply complete coverage of every documentation variant.
