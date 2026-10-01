<?php

declare(strict_types=1);

namespace App\Tests\Torture\Performance;

use App\Tests\Torture\Support\{ExpectedTortureGap, TortureTestCase};
use PHPUnit\Framework\Attributes\Group;

#[Group('performance')]
final class HighCardinalityTest extends TortureTestCase
{
    protected string $dataset = 'medium';
    protected ?int $rows = 20000;

    public function testTenThousandLinkageIdentifiersAndMutation(): void
    {
        $root = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'));
        self::assertCount(10000, $root['data']['relationships']['tasks']['data']);
        self::assertLessThan(256 * 1024 * 1024, $this->lastMetric()['peak_bytes'] - $this->lastMetric()['baseline_bytes']);
        $linkage = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/relationships/tasks?page[size]=100'));
        self::assertCount(100, $linkage['data']);
        $related = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/tasks?page[size]=20&fields[tasks]=title'));
        self::assertCount(20, $related['data']);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/tasks/20000/relationships/project', ['data' => ['type' => 'projects', 'id' => 'p-1']]));
        $changed = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'));
        self::assertCount(10001, $changed['data']['relationships']['tasks']['data']);
    }

    public function testWhenIncludedPolicyAvoidsEnumeratingTenThousandIds(): void
    {
        $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'));
        $always = $this->lastMetric();
        static::ensureKernelShutdown();
        $this->client = static::createClient(['environment' => 'torture_lean', 'debug' => false]);
        $lean = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'));
        self::assertArrayNotHasKey('data', $lean['data']['relationships']['tasks']);
        self::assertLessThan($always['response_bytes'] / 10, $this->lastMetric()['response_bytes']);
        self::assertLessThan($always['rows_fetched'], $this->lastMetric()['rows_fetched']);
    }

    #[Group('torture-gap')]
    #[ExpectedTortureGap('SCALABILITY-INCLUDE-AMPLIFICATION')]
    public function testDenseIncludeIsRejectedBeforeMassHydration(): void
    {
        $registry = self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class);
        $this->ids = (new \App\Torture\Infrastructure\TortureFixtures($registry))->reset('small', 1000);
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/organizations/org-1?include=projects.tasks.labels,projects.tasks.assignee'), 400);
        self::assertLessThanOrEqual(2000, $this->lastMetric()['rows_fetched'], 'A 250-resource include cap must stop traversal before hydrating a 1k-task graph and all its linkage.');
    }
}
