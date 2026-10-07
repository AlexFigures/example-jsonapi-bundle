<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};

final class NegotiationOptionsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_profile_options'; }
    public function testGlobalDefaultProfileRunsWithoutNegotiationAndEchoCanBeDisabled(): void
    {
        $response = $this->requestJsonApi('GET', '/api/articles');
        $doc = $this->decodeJsonApi($response);
        self::assertTrue($doc['meta']['cookbook_profile']);
        self::assertCount(2, $doc['data']);
        self::assertStringNotContainsString('profile=', (string) $response->headers->get('Content-Type'));
        self::assertFalse($response->headers->has('Link'));
    }
    public function testCustomRelationshipCountKeyIsUsed(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url(), headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"']));
        self::assertArrayHasKey('cardinality', $doc['data']['relationships']['tags']['meta']);
        self::assertSame(count($doc['data']['relationships']['tags']['data']), $doc['data']['relationships']['tags']['meta']['cardinality']);
        self::assertArrayNotHasKey('count', $doc['data']['relationships']['tags']['meta']);
    }
}
