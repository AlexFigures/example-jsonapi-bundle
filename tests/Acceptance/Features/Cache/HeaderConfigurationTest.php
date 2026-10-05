<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

final class HeaderConfigurationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_cache'; }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-SURROGATE-ROUTES')]
    public function testConfiguredCacheHeadersAndSurrogateResource(): void
    {
        $response = $this->requestJsonApi('GET', $this->url());
        $this->decodeJsonApi($response);
        foreach (['private', 'max-age=15', 's-maxage=30', 'stale-while-revalidate=45', 'stale-if-error=60'] as $directive) {
            self::assertStringContainsString($directive, (string) $response->headers->get('Cache-Control'));
        }
        self::assertTrue($response->headers->has('Age'));
        self::assertStringContainsString('Accept-Language', (string) $response->headers->get('Vary'));
        self::assertMatchesRegularExpression('/^"[a-f0-9]{64}"$/', (string) $response->headers->get('ETag'));
        self::assertStringContainsString('item-articles-'.$this->ids['article-1'], (string) $response->headers->get('X-Cache-Tags'));
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-SURROGATE-ROUTES')]
    public function testStrongCollectionValidatorAndCollectionKey(): void
    {
        $response = $this->requestJsonApi('GET', '/api/articles');
        $this->decodeJsonApi($response);
        self::assertStringStartsWith('"', (string) $response->headers->get('ETag'));
        self::assertStringContainsString('list-articles', (string) $response->headers->get('X-Cache-Tags'));
    }

    public function testHeadAndGetValidatorsAgree(): void
    {
        $get = $this->requestJsonApi('GET', $this->url());
        $head = $this->requestJsonApi('HEAD', $this->url());
        self::assertSame($get->headers->get('ETag'), $head->headers->get('ETag'));
        self::assertSame($get->headers->get('Last-Modified'), $head->headers->get('Last-Modified'));
        self::assertSame('', $head->getContent());
    }
}
