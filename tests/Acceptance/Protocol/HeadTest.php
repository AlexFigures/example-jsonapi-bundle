<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class HeadTest extends AcceptanceTestCase
{
    #[DataProvider('routeCategories')]
    public function testHeadHasGetHeadersAndNoBody(string $suffix): void
    {
        $url = $suffix === 'collection' ? '/api/articles' : $this->url().$suffix;
        $get = $this->requestJsonApi('GET', $url);
        self::assertSame(200, $get->getStatusCode());
        $head = $this->requestJsonApi('HEAD', $url);
        self::assertSame(200, $head->getStatusCode());
        self::assertSame('', $head->getContent());
        foreach (['Content-Type','ETag'] as $header) { self::assertSame($get->headers->get($header), $head->headers->get($header)); }
        self::assertContains('Accept', $head->getVary());
    }

    public static function routeCategories(): iterable
    {
        yield ['collection']; yield ['']; yield ['/relationships/author']; yield ['/tags'];
    }
}
