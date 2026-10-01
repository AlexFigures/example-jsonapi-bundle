<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Resource;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ResourceReadTest extends AcceptanceTestCase
{
    public function testCollection(): void
    {
        $doc = $this->collection();
        $this->assertJsonApiDocument($doc);
        self::assertCount(5, $doc['data']);
        foreach ($doc['data'] as $resource) {
            $this->assertResourceObject($resource, 'articles');
        }
    }

    public function testEmptyCollection(): void
    {
        $doc = $this->collection(['filter' => ['slug' => 'absent']]);
        self::assertSame([], $doc['data']);
    }

    public function testResource(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        $this->assertJsonApiDocument($doc);
        $this->assertResourceObject($doc['data'], 'articles', $this->ids['article-1']);
        self::assertSame('Shared title', $doc['data']['attributes']['title']);
        self::assertArrayNotHasKey('id', $doc['data']['attributes']);
    }

    public function testMissingResource(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles/99999999'), 404);
    }

    public function testUnknownType(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/unknown'), 404);
    }
}
