<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class RelatedResourcesTest extends AcceptanceTestCase
{
    public function testRelatedCollectionContainsResourceObjects(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/tags'));
        self::assertCount(2, $doc['data']);
        foreach ($doc['data'] as $resource) { $this->assertResourceObject($resource, 'tags'); }
    }

    public function testEmptyRelatedCollection(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('empty-author', 'authors').'/articles'));
        self::assertSame([], $doc['data']);
    }

    public function testNestedRelatedCollection(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('root', 'categories').'/children?include=children'));
        self::assertSame($this->ids['child'], $doc['data'][0]['id']);
        self::assertSame($this->ids['leaf'], $doc['included'][0]['id']);
    }
    public function testRelatedToOneRepresentation(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/author'));
        $this->assertResourceObject($doc['data'], 'authors', $this->ids['ada']);
        self::assertSame('Ada Lovelace', $doc['data']['attributes']['name']);
    }

}
