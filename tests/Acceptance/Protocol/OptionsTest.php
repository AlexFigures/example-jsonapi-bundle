<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class OptionsTest extends AcceptanceTestCase
{
    #[DataProvider('routes')]
    public function testAllowHeader(string $suffix, array $methods): void
    {
        $url = $suffix === 'collection' ? '/api/articles' : $this->url().$suffix;
        $response = $this->requestJsonApi('OPTIONS', $url);
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        $actual = array_map('trim', explode(',', (string) $response->headers->get('Allow')));
        self::assertEqualsCanonicalizing($methods, $actual);
    }

    public static function routes(): iterable
    {
        yield ['collection', ['GET','HEAD','POST','OPTIONS']];
        yield ['', ['GET','HEAD','PATCH','DELETE','OPTIONS']];
        yield ['/relationships/tags', ['GET','HEAD','POST','PATCH','DELETE','OPTIONS']];
        yield ['/relationships/author', ['GET','HEAD','PATCH','OPTIONS']];
        yield ['/tags', ['GET','HEAD','OPTIONS']];
    }
}
