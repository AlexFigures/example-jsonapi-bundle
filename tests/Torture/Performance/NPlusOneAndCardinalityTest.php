<?php

declare(strict_types=1);

namespace App\Tests\Torture\Performance;

use App\Tests\Torture\Support\{ExpectedTortureGap, TortureTestCase};
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('performance')]
final class NPlusOneAndCardinalityTest extends TortureTestCase
{
    protected string $dataset = 'small';
    protected ?int $rows = 1000;

    #[Group('torture-gap')]
    #[ExpectedTortureGap('PERFORMANCE-NPLUS1')]
    public function testCollectionWithoutIncludeHasBoundedQueryShape(): void
    {
        $this->collection(['page' => ['size' => 5]], 'tasks');
        $small = $this->lastMetric()['query_count'];
        $this->collection(['page' => ['size' => 20]], 'tasks');
        self::assertLessThanOrEqual($small + 10, $this->lastMetric()['query_count'], 'A larger page must not cause one lazy collection query per root.');
        self::assertLessThanOrEqual(12, $this->lastMetric()['query_count']);
        self::assertGreaterThan(0, $this->lastMetric()['response_bytes']);
    }

    #[Group('torture-gap')]
    #[ExpectedTortureGap('PERFORMANCE-NPLUS1')]
    #[DataProvider('includeCases')]
    public function testIncludeQueryCountDoesNotGrowWithPageSize(string $include, int $budget): void
    {
        $this->collection(['include' => $include, 'page' => ['size' => 5]], 'tasks');
        $small = $this->lastMetric()['query_count'];
        $this->collection(['include' => $include, 'page' => ['size' => 20]], 'tasks');
        self::assertLessThanOrEqual($small + 10, $this->lastMetric()['query_count']);
        self::assertLessThanOrEqual($budget, $this->lastMetric()['query_count'], sprintf('N+1 budget exceeded for %s: %s', $include, json_encode($this->lastMetric())));
    }

    public static function includeCases(): array
    {
        return ['to-one' => ['project,assignee', 18], 'to-many' => ['labels,attachments', 24], 'nested' => ['project.organization,project.memberships.user', 32]];
    }

    #[Group('torture-gap')]
    #[ExpectedTortureGap('PERFORMANCE-NPLUS1')]
    public function testRelatedCollectionQueryCountIsBounded(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/tasks?page[size]=20&sort=id'));
        self::assertCount(20, $doc['data']);
        self::assertLessThanOrEqual(15, $this->lastMetric()['query_count']);
    }

    public function testSparseCollectionAvoidsUnrequestedRelationshipHydration(): void
    {
        $doc = $this->collection(['fields' => ['tasks' => 'title'], 'page' => ['size' => 20]], 'tasks');
        self::assertCount(20, $doc['data']);
        self::assertLessThanOrEqual(6, $this->lastMetric()['query_count']);
    }
}
