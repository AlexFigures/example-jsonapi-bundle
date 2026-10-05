<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class ErrorConfigurationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_errors'; }
    public function testDebugMetadataCanBeEnabledAndCorrelationDisabledExplicitly(): void
    {
        $response = $this->requestJsonApi('GET', '/cookbook/errors/internal');
        $doc = $this->configuredError($response, 500);
        self::assertFalse($response->headers->has('X-Request-ID'));
        self::assertArrayNotHasKey('id', $doc['errors'][0]);
        self::assertSame('RuntimeException', $doc['errors'][0]['meta']['exceptionClass']);
        self::assertSame('Cookbook private diagnostic.', $doc['errors'][0]['meta']['message']);
        self::assertArrayHasKey('trace', $doc['errors'][0]['meta']);
    }
    public function testDefaultTitleMappingCanBeDisabled(): void
    {
        $doc = $this->configuredError($this->requestJsonApi('GET', $this->url().'?filter[unknown]=x'), 400);
        self::assertArrayNotHasKey('title', $doc['errors'][0]);
    }
    private function configuredError(\Symfony\Component\HttpFoundation\Response $response, int $status): array
    {
        self::assertSame($status, $response->getStatusCode());
        self::assertStringStartsWith(self::MEDIA, (string) $response->headers->get('Content-Type'));
        $doc = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($doc['errors']);
        self::assertSame((string) $status, $doc['errors'][0]['status']);
        return $doc;
    }

}
