<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Composition;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class ProfileRepresentationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features'; }

    public function testProfileIncludeCountsNeverLinkageAndCachingCompose(): void
    {
        $headers = ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"'];
        $response = $this->requestJsonApi('GET', $this->url().'?include=tags', headers: $headers);
        $doc = $this->decodeJsonApi($response);
        self::assertCount(2, $doc['included']);
        self::assertCount(2, $doc['data']['relationships']['tags']['data']);
        self::assertSame(2, $doc['data']['relationships']['tags']['meta']['count']);
        self::assertNotNull($response->headers->get('ETag'));
        $conditional = $this->requestJsonApi('GET', $this->url().'?include=tags', headers: $headers + ['If-None-Match' => $response->headers->get('ETag')]);
        self::assertSame(304, $conditional->getStatusCode());
    }
}
