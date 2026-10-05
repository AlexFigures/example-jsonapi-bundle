<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\{DataProvider, Group};

final class VersionStrategyTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_cache_version'; }
    #[DataProvider('versions')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-VERSION-STRATEGY')]
    public function testConfiguredVersionStrategyUsesApplicationVersion(string $version, ?string $etag): void
    {
        $response = $this->requestJsonApi('GET', '/cookbook/cache-version/'.$version);
        $this->decodeJsonApi($response);
        self::assertSame($etag, $response->headers->get('ETag'));
    }
    public static function versions(): iterable
    {
        yield 'version header' => ['7', '"7"'];
        yield 'no version' => ['absent', null];
    }
}
