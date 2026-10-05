<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledCacheTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_cache_disabled'; }
    public function testDisabledCacheDoesNotGenerateValidatorsOrConditionalResponse(): void
    {
        $response = $this->requestJsonApi('GET', $this->url());
        $this->decodeJsonApi($response);
        self::assertNull($response->headers->get('ETag'));
        self::assertNull($response->headers->get('Last-Modified'));
        $conditional = $this->requestJsonApi('GET', $this->url(), headers: ['If-None-Match' => '*', 'If-Modified-Since' => 'Fri, 01 Jan 2100 00:00:00 GMT']);
        $this->decodeJsonApi($conditional);
    }
}
