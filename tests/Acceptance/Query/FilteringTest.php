<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class FilteringTest extends AcceptanceTestCase
{
    #[DataProvider('filters')]
    public function testSupportedOperators(array $filter, array $sequences): void
    {
        $doc = $this->collection(['filter' => $filter, 'sort' => 'views', 'page' => ['size' => 20]]);
        self::assertSame($sequences, array_map(static fn (array $r): int => $r['attributes']['metadata']['sequence'], $doc['data']));
    }

    #[DataProvider('filtersBundleGaps')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('FILTER-001', ['neq public operator', 'ne'])]
    #[ExpectedBundleGap('FILTER-002', ['between'])]
    #[ExpectedBundleGap('FILTER-003', ['null', 'not null'])]
    #[ExpectedBundleGap('FILTER-004', ['ilike'])]
    #[ExpectedBundleGap('ALIAS-001', ['datetime alias'])]
    public function testSupportedOperatorsBundleGap(array $filter, array $sequences): void
    {
        $doc = $this->collection(['filter' => $filter, 'sort' => 'views', 'page' => ['size' => 20]]);
        self::assertSame($sequences, array_map(static fn (array $r): int => $r['attributes']['metadata']['sequence'], $doc['data']));
    }

    private static function filtersAll(): iterable
    {
        yield 'eq' => [['views' => ['eq' => 20]], [2]];
        yield 'neq public operator' => [['views' => ['neq' => 20]], [1,3,4,5,6,7,8,9,10,11,12]];
        yield 'ne' => [['views' => ['ne' => 20]], [1,3,4,5,6,7,8,9,10,11,12]];
        yield 'gt' => [['views' => ['gt' => 100]], [11,12]];
        yield 'gte' => [['views' => ['gte' => 100]], [10,11,12]];
        yield 'lt' => [['views' => ['lt' => 30]], [1,2]];
        yield 'lte' => [['views' => ['lte' => 30]], [1,2,3]];
        yield 'in' => [['views' => ['in' => [20,40]]], [2,4]];
        yield 'nin' => [['views' => ['nin' => [10,20,30,40,50,60,70,80,90,100]]], [11,12]];
        yield 'like' => [['title' => ['like' => 'Article 0%']], [3,4,5,6,7,8,9]];
        yield 'ilike' => [['title' => ['ilike' => 'article 0%']], [3,4,5,6,7,8,9]];
        yield 'between' => [['views' => ['between' => [20,40]]], [2,3,4]];
        yield 'null' => [['published-at' => ['isnull' => 'true']], [1,3,5,7,9,11]];
        yield 'not null' => [['published-at' => ['isnull' => 'false']], [2,4,6,8,10,12]];
        yield 'enum' => [['status' => 'published'], [2,4,6,8,10,12]];
        yield 'boolean' => [['featured' => ['eq' => '1']], [2,4,6,8,10,12]];
        yield 'datetime alias' => [['published-at' => ['gte' => '2026-01-02T00:00:00+00:00']], [2,4,6,8,10,12]];
        yield 'relationship path' => [['author.name' => ['eq' => 'Grace Hopper']], [9,10,11,12]];
        yield 'AND' => [['and' => [['views' => ['gt' => 20]], ['views' => ['lt' => 50]]]], [3,4]];
        yield 'OR' => [['or' => [['views' => 10], ['views' => 120]]], [1,12]];
        yield 'nested groups' => [['and' => [['or' => [['views' => 10], ['views' => 20]]], ['status' => 'published']]], [2]];
        yield 'SQL sensitive literal' => [['title' => ['eq' => "' OR 1=1 --"]], []];
        yield 'wildcard matching' => [['title' => ['like' => '%title']], [1,2]];
    }

    public function testSqlSensitiveValueCanRoundTripAndFilter(): void
    {
        $literal = "Quote ' and % underscore _";
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => $literal])));
        $doc = $this->collection(['filter' => ['title' => ['eq' => $literal]]]);
        self::assertCount(1, $doc['data']);
        self::assertSame($literal, $doc['data'][0]['attributes']['title']);
    }

    public static function filters(): iterable
    {
        foreach (self::filtersAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (!in_array($label, ['neq public operator', 'ne', 'ilike', 'between', 'null', 'not null', 'datetime alias'], true)) { yield $label => $row; }
        }
    }

    public static function filtersBundleGaps(): iterable
    {
        foreach (self::filtersAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (in_array($label, ['neq public operator', 'ne', 'ilike', 'between', 'null', 'not null', 'datetime alias'], true)) { yield $label => $row; }
        }
    }
}
