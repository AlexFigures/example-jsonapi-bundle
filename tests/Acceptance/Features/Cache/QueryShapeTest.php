<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class QueryShapeTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_cache_queryoff'; }
    public function testEquivalentRepresentationsIgnoreQueryShapeWhenDisabled(): void
    {
        // Language headers participate in the cache-key shape; this resource has no translated attributes.
        $first = $this->requestJsonApi('GET', '/cookbook/cache-version/7', headers: ['Accept-Language' => 'en']);
        $second = $this->requestJsonApi('GET', '/cookbook/cache-version/7', headers: ['Accept-Language' => 'fr']);
        self::assertSame($first->getContent(), $second->getContent());
        self::assertSame(200, $second->getStatusCode());
        self::assertNotNull($first->headers->get('ETag'));
        self::assertSame($first->headers->get('ETag'), $second->headers->get('ETag'));
    }
}
