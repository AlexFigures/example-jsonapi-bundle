<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class OpenApiTest extends AcceptanceTestCase
{
    private function specification(): array
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-OPERATIONS')]
    public function testGeneratedResourceOperations(): void
    {
        $spec = $this->specification();
        self::assertStringStartsWith('3.', $spec['openapi']);
        self::assertSame('My API', $spec['info']['title']);
        self::assertSame('1.0.0', $spec['info']['version']);
        self::assertSame(['get', 'post'], array_keys($spec['paths']['/api/articles']));
        foreach (['get', 'patch', 'delete'] as $method) {
            self::assertArrayHasKey($method, $spec['paths']['/api/articles/{id}']);
        }
        self::assertArrayNotHasKey('post', $spec['paths']['/api/audit-logs']);
        self::assertArrayNotHasKey('/api/author-publishing-statistics', $spec['paths']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-OPERATIONS')]
    public function testCustomOnlyResourceDoesNotAdvertiseCrud(): void
    {
        $spec = $this->specification();
        self::assertArrayNotHasKey('/api/author-publishing-statistics', $spec['paths']);
        self::assertArrayNotHasKey('/api/author-publishing-statistics/{id}', $spec['paths']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-INHERITANCE')]
    public function testInheritedWhitelistIsExpandedInDocumentation(): void
    {
        $spec = $this->specification();
        $parameters = $spec['paths']['/api/feature-articles']['get']['parameters'];
        $names = array_column($parameters, 'name');
        self::assertContains('filter[author.name][eq]', $names);
        self::assertContains('filter[author.email][eq]', $names);
        self::assertContains('filter[tags.name][eq]', $names);
        self::assertNotContains('filter[editor.email][eq]', $names);
        $sort = array_values(array_filter($parameters, static fn (array $p): bool => $p['name'] === 'sort'))[0];
        self::assertStringContainsString('author.name', $sort['description']);
        self::assertStringNotContainsString('editor.email', $sort['description']);
    }

    public function testSchemaPrimitiveTypesAndEnumMatchRepresentation(): void
    {
        $spec = $this->specification();
        $attributes = $spec['components']['schemas']['ArticlesResource']['properties']['attributes']['properties'];
        self::assertSame('boolean', $attributes['featured']['type']);
        self::assertSame('integer', $attributes['views']['type']);
        self::assertSame('number', $attributes['rating']['type']);
        self::assertSame('date-time', $attributes['published-at']['format']);
        self::assertTrue($attributes['published-at']['nullable']);
        self::assertEqualsCanonicalizing(['draft', 'published', 'archived'], $attributes['status']['enum']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-WRITABLE-SCHEMA')]
    public function testReadOnlyAttributesAreNotAdvertisedAsWritable(): void
    {
        $spec = $this->specification();
        $attributes = $spec['components']['schemas']['ArticlesResource']['properties']['attributes']['properties'];
        self::assertTrue($attributes['createdAt']['readOnly'] ?? false, 'Server-owned timestamps must be readOnly in the public schema.');
        self::assertTrue($attributes['updatedAt']['readOnly'] ?? false);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-PAGINATION-CONFIG')]
    public function testPaginationDocumentationUsesEffectiveConfiguration(): void
    {
        $spec = $this->specification();
        $parameters = $spec['paths']['/api/articles']['get']['parameters'];
        $size = array_values(array_filter($parameters, static fn (array $p): bool => $p['name'] === 'page[size]'))[0];
        self::assertSame(5, $size['schema']['default']);
        self::assertSame(20, $size['schema']['maximum']);
    }

    public function testCustomHandlerRoutesAreDocumented(): void
    {
        $spec = $this->specification();
        self::assertArrayHasKey('/api/articles/{id}/publish', $spec['paths']);
        self::assertArrayHasKey('post', $spec['paths']['/api/articles/{id}/publish']);
    }

    public function testControllerAttributesAreDocumented(): void
    {
        $spec = $this->specification();
        $endpoint = $spec['paths']['/cookbook/responses/{form}']['get'];
        self::assertSame('Response factory cookbook', $endpoint['summary']);
        self::assertSame('cookbookResponse', $endpoint['operationId']);
        self::assertTrue($endpoint['deprecated']);
        self::assertSame(['Cookbook'], $endpoint['tags']);
        self::assertCount(3, $endpoint['parameters']);
        self::assertArrayHasKey('requestBody', $endpoint);
        self::assertArrayHasKey('X-Cookbook', $endpoint['responses']['200']['headers']);
        self::assertSame('#/components/schemas/AuthorsResource', $endpoint['responses']['200']['content'][self::MEDIA]['schema']['$ref']);
        self::assertSame([['bearerAuth' => []]], $endpoint['security']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-ENDPOINT-EXAMPLES')]
    public function testPublicEndpointExamplesArePresentInSpec(): void
    {
        $spec = $this->specification();
        $endpoint = $spec['paths']['/cookbook/responses/{form}']['get'];
        self::assertStringContainsString('Example input', json_encode($endpoint, JSON_THROW_ON_ERROR));
    }

    public function testSwaggerShellReferencesConfiguredSpecification(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/docs', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('/_jsonapi/openapi.json', (string) $response->getContent());
        self::assertStringContainsString('SwaggerUI', (string) $response->getContent());
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-NEGOTIATION')]
    #[DataProvider('documentationMedia')]
    public function testDocumentationAcceptsItsNativeMediaType(string $url, string $media): void
    {
        $response = $this->requestJsonApi('GET', $url, headers: ['Accept' => $media]);
        self::assertSame(200, $response->getStatusCode());
    }

    public static function documentationMedia(): iterable
    {
        yield 'OpenAPI native' => ['/_jsonapi/openapi.json', 'application/vnd.oai.openapi+json'];
        yield 'OpenAPI JSON' => ['/_jsonapi/openapi.json', 'application/json'];
        yield 'UI HTML' => ['/_jsonapi/docs', 'text/html'];
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CONFIG-JSON-SCHEMA')]
    public function testEnabledJsonSchemaRouteExists(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/schemas', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode(), 'docs.generator.json_schema.enabled defaults to true; configured route must exist.');
    }
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DOCS-OPERATIONS')]
    public function testSelectiveOperationsMatchActualCollectionAndItemRoutes(): void
    {
        $spec = $this->specification();
        // Path Item metadata (such as shared parameters) is not an HTTP operation.
        $verbs = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];
        $methods = static fn (array $path): array => array_values(array_intersect(array_keys($path), $verbs));
        self::assertSame(['post'], $methods($spec['paths']['/api/feature-records']));
        self::assertEqualsCanonicalizing(['get', 'patch'], $methods($spec['paths']['/api/feature-records/{id}']));
    }

}
