<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class ConfiguredRoutesTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_docs_routes'; }
    public function testDocumentationRoutesAndUiSpecUrlAreConfigurable(): void
    {
        foreach (['/reference/openapi.json' => 'application/vnd.oai.openapi+json', '/reference/schema.json' => 'application/schema+json', '/reference/docs' => 'text/html'] as $url => $media) {
            $response = $this->requestJsonApi('GET', $url, headers: ['Accept' => $media]);
            self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
            self::assertStringContainsString($media, (string) $response->headers->get('Content-Type'));
            if ($media === 'text/html') { self::assertStringContainsString('/reference/openapi.json', (string) $response->getContent()); }
        }
        foreach (['/_jsonapi/openapi.json', '/_jsonapi/schemas', '/_jsonapi/docs'] as $url) {
            self::assertSame(404, $this->requestJsonApi('GET', $url, headers: ['Accept' => '*/*'])->getStatusCode());
        }
    }
}
