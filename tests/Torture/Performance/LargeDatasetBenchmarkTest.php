<?php

declare(strict_types=1);

namespace App\Tests\Torture\Performance;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('performance')]
final class LargeDatasetBenchmarkTest extends TortureTestCase
{
    protected string $dataset = 'small';

    public function testConfigurableLargeDatasetBenchmark(): void
    {
        $rows = max(20000, min(1000000, (int) (getenv('TORTURE_ROWS') ?: 100000)));
        $registry = self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class);
        $this->ids = (new \App\Torture\Infrastructure\TortureFixtures($registry))->reset($rows >= 100000 ? 'large' : ($rows >= 10000 ? 'medium' : 'small'), $rows);
        foreach ([1, 10, 100, 1000] as $page) {
            $doc = $this->collection(['page' => ['number' => $page, 'size' => 20], 'sort' => 'title,id', 'fields' => ['tasks' => 'title']], 'tasks');
            self::assertCount(20, $doc['data']);
            self::assertLessThanOrEqual(6, $this->lastMetric()['query_count']);
            file_put_contents(dirname(__DIR__, 3).'/var/torture/large-benchmark.ndjson', json_encode([
                'rows' => $rows, 'page' => $page, 'metric' => $this->lastMetric(),
            ], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
        }
        self::assertNotEmpty($this->metrics);
    }
}
