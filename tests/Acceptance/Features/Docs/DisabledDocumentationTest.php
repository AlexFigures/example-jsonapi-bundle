<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledDocumentationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_docs_off'; }

    public function testJsonSchemaRemainsIndependentOfDisabledOpenApi(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/schemas', headers: ['Accept' => 'application/schema+json']);
        self::assertSame(200, $response->getStatusCode());
        $schema = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://json-schema.org/draft/2020-12/schema', $schema['$schema']);
        self::assertNotEmpty($schema['$defs']);
    }

    public function testDisabledSpecAndUiDoNotServeDocuments(): void
    {
        foreach (['/_jsonapi/openapi.json', '/_jsonapi/docs'] as $url) {
            self::assertSame(404, $this->requestJsonApi('GET', $url, headers: ['Accept' => '*/*'])->getStatusCode());
        }
    }
}
