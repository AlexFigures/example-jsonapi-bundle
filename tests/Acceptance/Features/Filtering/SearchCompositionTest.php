<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

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
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('FILTER-HANDLER-LOGICAL-COMPOSITION')]
    public function testHandlerInsideOrPreservesAlternativeNormalPredicate(): void
    {
        $doc = $this->collection(['filter' => ['or' => [['search' => 'No such term'], ['views' => 10]]]]);
        self::assertSame([$this->ids['article-1']], array_column($doc['data'], 'id'));
    }
}
