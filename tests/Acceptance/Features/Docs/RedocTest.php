<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class RedocTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features';
    }

    public function testConfiguredSpecIdentity(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode());
        $spec = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Feature cookbook', $spec['info']['title']);
        self::assertSame('0.9.0', $spec['info']['version']);
        self::assertSame('https://cookbook.example.test', $spec['servers'][0]['url']);
    }

    public function testRedocShell(): void
    {
        $response = $this->requestJsonApi('GET', '/_jsonapi/docs', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('redoc', strtolower((string) $response->getContent()));
        self::assertStringContainsString('/_jsonapi/openapi.json', (string) $response->getContent());
    }
}
