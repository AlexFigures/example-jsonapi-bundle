<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class SearchCompositionTest extends AcceptanceTestCase
{
    public function testRepeatedHandlerUsesDistinctBoundParameters(): void
    {
        $query = ['filter' => ['and' => [['search' => 'Article'], ['search' => 'Body']]], 'page' => ['size' => 20]];
        $doc = $this->collection($query);
        self::assertCount(10, $doc['data']);
        self::assertNotContains($this->ids['article-1'], array_column($doc['data'], 'id'));
        self::assertNotContains($this->ids['article-2'], array_column($doc['data'], 'id'));
    }
    #[DataProvider('logicalCases')]
    public function testHandlerInsideOrPreservesAlternativeNormalPredicate(array $filter, array $numbers): void
    {
        $doc = $this->collection(['filter' => $filter, 'page' => ['size' => 20]]);
        self::assertSame(array_map(fn (int $n): string => $this->ids['article-'.$n], $numbers), array_column($doc['data'], 'id'));
    }
    public static function logicalCases(): iterable
    {
        yield 'custom AND ordinary' => [['and' => [['search' => 'Body 1'], ['views' => 10]]], [1]];
        yield 'custom OR ordinary' => [['or' => [['search' => 'No such term'], ['views' => 10]]], [1]];
        yield 'ordinary OR custom' => [['or' => [['views' => 10], ['search' => 'No such term']]], [1]];
        yield 'nested alternatives' => [['or' => [['and' => [['views' => 20], ['search' => 'Body 2']]], ['and' => [['views' => 10], ['title' => 'Shared title']]]]], [1, 2]];
    }
}
