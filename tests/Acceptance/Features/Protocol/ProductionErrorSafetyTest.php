<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class ProductionErrorSafetyTest extends AcceptanceTestCase
{
    public function testInternalExceptionIsMaskedAndCorrelationIsObservable(): void
    {
        $response = $this->requestJsonApi('GET', '/cookbook/errors/internal');
        $doc = $this->assertJsonApiError($response, 500);
        self::assertTrue($response->headers->has('X-Request-ID'));
        self::assertSame($response->headers->get('X-Request-ID'), $doc['errors'][0]['id']);
        self::assertStringNotContainsString('Cookbook private diagnostic', (string) $response->getContent());
        self::assertArrayNotHasKey('trace', $doc['errors'][0]['meta'] ?? []);
        self::assertArrayNotHasKey('exceptionClass', $doc['errors'][0]['meta'] ?? []);
    }
}
