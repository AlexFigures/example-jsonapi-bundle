<?php

declare(strict_types=1);

namespace App\Tests\Torture\Extreme;

use App\Tests\Torture\Support\TortureTestCase;
use App\Tests\Torture\Support\ExpectedTortureGap;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('extreme')]
final class QueryAmplificationTest extends TortureTestCase
{
    public static function nodeCounts(): array { return ['100 nodes' => [100], '500 nodes' => [500], '1000 nodes' => [1000]]; }

    #[DataProvider('nodeCounts')]
    public function testFilterNodeBudgetRejectsBeforeSql(int $nodes): void
    {
        $filter = ['or' => array_fill(0, $nodes, ['title' => 'Same'])];
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/tasks?'.http_build_query(['filter' => $filter])), 400);
        self::assertSame(0, $this->lastMetric()['query_count']);
    }

    public function testFilterDepthBudgetRejectsBeforeSql(): void
    {
        $filter = ['title' => 'Same'];
        for ($i = 0; $i < 30; ++$i) { $filter = ['and' => [$filter]]; }
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/tasks?'.http_build_query(['filter' => $filter])), 400);
        self::assertSame(0, $this->lastMetric()['query_count']);
    }

    public function testLargeInListCannotBypassComplexityBudget(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/tasks?'.http_build_query(['filter' => ['title' => ['in' => array_fill(0, 500, 'Same')]]])), 400);
        self::assertSame(0, $this->lastMetric()['query_count']);
    }

    public function testReasonableBooleanAndRelationshipFilterWorks(): void
    {
        $doc = $this->collection(['filter' => ['and' => [['title' => 'Same'], ['or' => [['labels.name' => 'Label 1'], ['labels.name' => 'Label 2']]]]], 'sort' => 'id'], 'tasks');
        self::assertCount(5, $doc['data']);
    }
}
