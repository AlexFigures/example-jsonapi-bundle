<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\DataProvider;

final class RelatedCountOptionsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_counts_'.$this->dataName(); }
    #[ExpectedBundleGap('PROFILE-REL-COUNT-RELATED-POLICY', ['off'])]
    #[DataProvider('modes')]
    public function testRelatedEndpointCountPolicy(bool $enabled): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/authors/'.$this->ids['ada'].'/articles', headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"']));
        self::assertNotEmpty($doc['data']);
        foreach ($doc['data'] as $article) {
            $meta = $article['relationships']['tags']['meta'] ?? [];
            if ($enabled) { self::assertArrayHasKey('count', $meta); }
            else { self::assertArrayNotHasKey('count', $meta); }
        }
    }
    public static function modes(): iterable { yield 'on' => [true]; yield 'off' => [false]; }
}
