<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class SchemaProfilesTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_schema_profiles_off'; }

    public function testDisabledProfileCatalogIsAbsentWithoutDisablingSchema(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/schemas', headers: ['Accept' => 'application/schema+json']);
        self::assertSame(200, $response->getStatusCode());
        $schema = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('x-jsonapi-profiles', $schema);
        self::assertNotEmpty($schema['$defs']);
    }
}
