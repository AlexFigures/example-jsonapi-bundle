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

    public function testCustomOnlyResourceDoesNotAdvertiseCrud(): void
    {
        $spec = $this->specification();
        self::assertArrayNotHasKey('/api/author-publishing-statistics', $spec['paths']);
        self::assertArrayNotHasKey('/api/author-publishing-statistics/{id}', $spec['paths']);
    }

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

    public function testReadOnlyAttributesAreNotAdvertisedAsWritable(): void
    {
        $spec = $this->specification();
        $attributes = $spec['components']['schemas']['ArticlesResource']['properties']['attributes']['properties'];
        self::assertTrue($attributes['createdAt']['readOnly'] ?? false, 'Server-owned timestamps must be readOnly in the public schema.');
        self::assertTrue($attributes['updatedAt']['readOnly'] ?? false);
    }

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
        yield 'Schema native' => ['/_jsonapi/schemas', 'application/schema+json'];
        yield 'Schema JSON' => ['/_jsonapi/schemas', 'application/json'];
        yield 'UI HTML' => ['/_jsonapi/docs', 'text/html'];
    }

    public function testEnabledJsonSchemaRouteExists(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/schemas', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode(), 'docs.generator.json_schema.enabled defaults to true; configured route must exist.');
        $schema = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://json-schema.org/draft/2020-12/schema', $schema['$schema']);
        self::assertNotEmpty($schema['$defs']);
        self::assertContains('urn:example:profile:cookbook', $schema['x-jsonapi-profiles']);
        $check = function (array $node) use (&$check, $schema): void {
            if (isset($node['$ref']) && str_starts_with($node['$ref'], '#')) {
                self::assertStringStartsWith('#/$defs/', $node['$ref']);
                self::assertArrayHasKey(substr($node['$ref'], strlen('#/$defs/')), $schema['$defs']);
            }
            foreach ($node as $child) { if (is_array($child)) { $check($child); } }
        };
        $check($schema);
    }
    public function testSelectiveOperationsMatchActualCollectionAndItemRoutes(): void
    {
        $spec = $this->specification();
        // Path Item metadata (such as shared parameters) is not an HTTP operation.
        $verbs = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];
        $methods = static fn (array $path): array => array_values(array_intersect(array_keys($path), $verbs));
        self::assertSame(['post'], $methods($spec['paths']['/api/feature-records']));
        self::assertEqualsCanonicalizing(['get', 'patch'], $methods($spec['paths']['/api/feature-records/{id}']));
    }

    #[DataProvider('operationResources')]
    public function testHttpAllowAndOpenApiAdvertiseTheSameOperations(string $type, string $id): void
    {
        $spec = $this->specification();
        foreach (['/api/'.$type => '/api/'.$type, '/api/'.$type.'/{id}' => '/api/'.$type.'/'.$id] as $path => $url) {
            $response = $this->requestJsonApi('OPTIONS', $url);
            $documented = array_values(array_intersect(array_keys($spec['paths'][$path] ?? []), ['get', 'post', 'patch', 'delete', 'put']));
            if ($response->getStatusCode() === 404) {
                self::assertSame([], $documented);
                continue;
            }
            self::assertSame(204, $response->getStatusCode());
            $actual = array_values(array_intersect(array_map('strtolower', array_map('trim', explode(',', (string) $response->headers->get('Allow')))), ['get', 'post', 'patch', 'delete', 'put']));
            self::assertEqualsCanonicalizing($actual, $documented);
        }
    }
    public static function operationResources(): iterable
    {
        yield 'full CRUD' => ['articles', '1'];
        yield 'read only' => ['audit-logs', '1'];
        yield 'selective' => ['feature-records', '1'];
        yield 'custom only' => ['author-publishing-statistics', '1'];
    }
    public function testEveryCustomEndpointAttributeOptionIsRepresented(): void
    {
        $spec = $this->specification();
        $endpoint = $spec['paths']['/cookbook/responses/{form}']['get'];
        self::assertSame('Application controller using the public JSON:API response factory.', $endpoint['description']);
        $parameters = array_column($endpoint['parameters'], null, 'name');
        self::assertSame('Response form', $parameters['form']['description']);
        self::assertTrue($parameters['form']['required']);
        self::assertSame('resource', $parameters['form']['example']);
        self::assertSame(['type' => 'string'], $parameters['include']['schema']);
        self::assertSame('articles', $parameters['include']['example']);
        self::assertSame('uuid', $parameters['X-Cookbook']['schema']['format']);
        self::assertSame('string', $parameters['X-Cookbook']['schema']['type']);
        self::assertFalse($endpoint['requestBody']['required']);
        self::assertSame('Optional application input', $endpoint['requestBody']['description']);
        self::assertSame(['type' => 'object'], $endpoint['requestBody']['content'][self::MEDIA]['schema']);
        self::assertSame(['type' => 'object', 'properties' => ['queued' => ['type' => 'boolean']]], $endpoint['responses']['202']['content'][self::MEDIA]['schema']);
        $response = $endpoint['responses']['200'];
        self::assertSame('Author representation', $response['description']);
        self::assertSame('Sequence', $response['headers']['X-Cookbook-Sequence']['description']);
        self::assertSame(['type' => 'integer', 'format' => 'int64'], $response['headers']['X-Cookbook-Sequence']['schema']);
        $example = $endpoint['requestBody']['content'][self::MEDIA]['examples']['sample'];
        self::assertSame('Example input', $example['summary']);
        self::assertSame('An author identifier', $example['description']);
        self::assertSame(['data' => ['type' => 'authors']], $example['value']);
    }
}
