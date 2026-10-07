<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ConditionalRequestsTest extends AcceptanceTestCase
{
    public function testEtagAndIfNoneMatch(): void
    {
        $first = $this->requestJsonApi('GET', $this->url());
        $this->decodeJsonApi($first);
        self::assertNotEmpty($first->headers->get('ETag'));
        self::assertNotEmpty($first->headers->get('Last-Modified'));
        $cached = $this->requestJsonApi('GET', $this->url(), headers: ['If-None-Match' => $first->headers->get('ETag')]);
        self::assertSame(304, $cached->getStatusCode());
        self::assertSame('', $cached->getContent());
    }

    public function testChangedResourceInvalidatesEtag(): void
    {
        $first = $this->requestJsonApi('GET', $this->url());
        $etag = $first->headers->get('ETag');
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Changed representation'])));
        $after = $this->requestJsonApi('GET', $this->url(), headers: ['If-None-Match' => $etag]);
        $this->decodeJsonApi($after);
        self::assertNotSame($etag, $after->headers->get('ETag'));
    }

    #[DataProvider('queryShapes')]
    public function testRepresentationDependentValidators(string $query): void
    {
        $base = $this->requestJsonApi('GET', '/api/articles');
        $variant = $this->requestJsonApi('GET', '/api/articles?'.$query, headers: ['If-None-Match' => $base->headers->get('ETag')]);
        $this->decodeJsonApi($variant);
        self::assertNotSame($base->headers->get('ETag'), $variant->headers->get('ETag'));
    }

    public static function queryShapes(): iterable
    {
        yield ['fields[articles]=title']; yield ['include=author']; yield ['filter[status]=published'];
        yield ['sort=-title']; yield ['page[number]=2'];
    }

    public function testLastModifiedUsesConfiguredEntityField(): void
    {
        $first = $this->requestJsonApi('GET', $this->url());
        self::assertSame('Thu, 15 Jan 2026 00:00:00 GMT', $first->headers->get('Last-Modified'));
        $cached = $this->requestJsonApi('GET', $this->url(), headers: ['If-Modified-Since' => 'Thu, 15 Jan 2026 00:00:00 GMT']);
        self::assertSame(304, $cached->getStatusCode());
        self::assertSame('', $cached->getContent());
    }
}
