<?php

declare(strict_types=1);

namespace App\Tests\Torture\Extreme;

use App\Tests\Torture\Support\TortureTestCase;
use App\Tests\Torture\Support\ExpectedTortureGap;
use PHPUnit\Framework\Attributes\Group;

#[Group('extreme')]
final class PaginationUnderJoinsTest extends TortureTestCase
{
    #[Group('torture-gap')]
    #[ExpectedTortureGap('SCALABILITY-JOIN-PAGINATION')]
    public function testCartesianJoinsPaginateDistinctRoots(): void
    {
        $seen = [];
        foreach ([1, 3, 5] as $page) {
            $doc = $this->collection(['filter' => ['labels.name' => ['in' => ['Label 1', 'Label 2']]], 'sort' => 'attachments.name,id', 'include' => 'labels', 'page' => ['number' => $page, 'size' => 20]], 'tasks');
            $ids = array_column($doc['data'], 'id');
            self::assertCount(20, $ids, '20 root resources, not 20 joined rows.');
            self::assertCount(20, array_unique($ids));
            self::assertSame([], array_intersect($seen, $ids));
            $seen = [...$seen, ...$ids];
        }
    }

    public function testEqualSortValuesStayStableAcrossUnrelatedMutation(): void
    {
        $this->rows = 1000;
        $registry = self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class);
        $this->ids = (new \App\Torture\Infrastructure\TortureFixtures($registry))->reset('small', 1000);
        $before = [];
        foreach ([1, 2, 3] as $page) {
            $doc = $this->collection(['filter' => ['title' => 'Same'], 'sort' => 'title', 'page' => ['number' => $page, 'size' => 20]], 'tasks');
            $before[$page] = array_column($doc['data'], 'id');
        }
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/tasks/500', ['data' => ['type' => 'tasks', 'id' => '500', 'attributes' => ['description' => 'Unrelated update']]]));
        foreach ([1, 2, 3] as $page) {
            $doc = $this->collection(['filter' => ['title' => 'Same'], 'sort' => 'title', 'page' => ['number' => $page, 'size' => 20]], 'tasks');
            self::assertSame($before[$page], array_column($doc['data'], 'id'));
            self::assertSame(array_map('strval', range(($page - 1) * 20 + 1, $page * 20)), array_column($doc['data'], 'id'), 'Equal values require identifier ordering.');
        }
    }
}
