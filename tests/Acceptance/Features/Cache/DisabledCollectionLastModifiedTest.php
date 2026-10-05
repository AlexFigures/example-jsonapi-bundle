<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

final class DisabledCollectionLastModifiedTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_lastmodified_off'; }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-COLLECTION-LAST-MODIFIED')]
    public function testDisablingCollectionMaximumDoesNotSynthesizeCollectionValidator(): void
    {
        $item = $this->requestJsonApi('GET', $this->url());
        self::assertNotNull($item->headers->get('Last-Modified'));
        $collection = $this->requestJsonApi('GET', '/api/articles');
        $this->decodeJsonApi($collection);
        self::assertNull($collection->headers->get('Last-Modified'));
    }
}
