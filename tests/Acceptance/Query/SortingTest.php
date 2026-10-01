<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class SortingTest extends AcceptanceTestCase
{
    #[DataProvider('sorts')]
    public function testSort(string $sort, string $field, bool $descending): void
    {
        $doc = $this->collection(['sort' => $sort, 'page' => ['size' => 20]]);
        $values = array_column(array_column($doc['data'], 'attributes'), $field);
        $expected = $values;
        $descending ? rsort($expected) : sort($expected);
        self::assertSame($expected, $values);
    }

    public static function sorts(): iterable
    {
        yield ['title', 'title', false];
        yield ['-title', 'title', true];
        yield ['title,-createdAt', 'title', false];
        yield ['views', 'views', false];
        yield ['title,title', 'title', false];
    }

    public function testRelationshipPropertySort(): void
    {
        $doc = $this->collection(['sort' => 'author.name,id', 'include' => 'author', 'page' => ['size' => 20]]);
        self::assertSame($this->ids['ada'], $doc['data'][0]['relationships']['author']['data']['id']);
        self::assertSame($this->ids['grace'], $doc['data'][11]['relationships']['author']['data']['id']);
    }

    public function testExplicitTiebreakerAcrossPages(): void
    {
        $first = $this->collection(['sort' => 'title,id', 'page' => ['size' => 11]]);
        $last = $this->collection(['sort' => 'title,id', 'page' => ['size' => 11, 'number' => 2]]);
        self::assertCount(12, array_unique(array_merge(array_column($first['data'], 'id'), array_column($last['data'], 'id'))));
    }

    #[ExpectedBundleGap('ALIAS-001')]
    public function testNullableAliasSort(): void
    {
        $doc = $this->collection(['sort' => 'published-at,id', 'page' => ['size' => 20]]);
        self::assertCount(12, $doc['data']);
        $values = array_column(array_column($doc['data'], 'attributes'), 'published-at');
        self::assertCount(6, array_filter($values, static fn ($v): bool => $v === null));
    }
    #[ExpectedBundleGap('SORT-001')]
    public function testImplicitIdTiebreakerSurvivesUpdates(): void
    {
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['content' => 'Physical row changed'])));
        $doc = $this->collection(['filter' => ['title' => 'Shared title'], 'sort' => 'title', 'page' => ['size' => 20]]);
        self::assertSame([$this->ids['article-1'], $this->ids['article-2']], array_column($doc['data'], 'id'));
    }

}
