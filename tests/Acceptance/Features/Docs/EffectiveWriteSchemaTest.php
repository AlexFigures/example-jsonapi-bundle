<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\ProductionTestCase;
use App\Tests\Acceptance\Production\WriteSurfaceTest;

/** Compare documentation to successful/rejected generated HTTP writes, with production groups. */
final class EffectiveWriteSchemaTest extends ProductionTestCase
{
    public function testEffectiveWriteSurfaceMatchesOpenApi(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => 'application/json']);
        self::assertSame(200, $response->getStatusCode());
        $spec = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $resource = $spec['components']['schemas']['ArticlesResource']['properties'];
        $attributes = $resource['attributes']['properties'];
        foreach (WriteSurfaceTest::protectedAttributes() as [$field, $value]) {
            $this->assertJsonApiError($this->asUser('admin', 'PATCH', $this->url(), $this->patchPayload([$field => $value])), 422, '/data/attributes/'.$field);
            self::assertTrue($attributes[$field]['readOnly'] ?? false, $field.' must not be advertised as writable.');
        }
        $accepted = ['title' => 'Documented writable article', 'slug' => 'documented-writable', 'content' => 'Documented body', 'featured' => true, 'metadata' => ['example' => true]];
        $doc = $this->decodeJsonApi($this->asUser('admin', 'PATCH', $this->url(), $this->patchPayload($accepted)));
        foreach ($accepted as $field => $value) {
            self::assertSame($value, $doc['data']['attributes'][$field]);
            self::assertArrayHasKey($field, $attributes);
            self::assertFalse($attributes[$field]['readOnly'] ?? false);
        }
        self::assertArrayHasKey('author', $resource['relationships']['properties']);
        $payload = $this->patchPayload(new \stdClass());
        $payload['data']['relationships']['author'] = ['data' => $this->identifier('grace', 'authors')];
        $updated = $this->decodeJsonApi($this->asUser('admin', 'PATCH', $this->url(), $payload));
        self::assertSame($this->ids['grace'], $updated['data']['relationships']['author']['data']['id']);
    }
}
