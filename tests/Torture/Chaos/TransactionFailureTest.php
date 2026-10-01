<?php

declare(strict_types=1);

namespace App\Tests\Torture\Chaos;

use App\Tests\Torture\Support\{ExpectedTortureGap, TortureTestCase};
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('chaos')]
final class TransactionFailureTest extends TortureTestCase
{
    public static function sqlStates(): array
    {
        return ['serialization' => ['40001'], 'deadlock' => ['40P01'], 'lock timeout' => ['55P03']];
    }

    #[DataProvider('sqlStates')]
    public function testDriverFailureRollsBackCompleteAtomicBatch(string $state): void
    {
        $response = $this->atomic([
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'First must rollback']]],
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-2'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Second fails']]],
        ], ['X-Torture-Fault' => 'sqlstate:'.$state]);
        $document = $this->assertJsonApiError($response, 500);
        self::assertStringNotContainsString('Injected', json_encode($document, JSON_THROW_ON_ERROR));
        self::assertSame('Primary v2 project 1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
        self::assertSame('Primary v2 project 2', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-2'))['data']['attributes']['name']);
    }

    public function testActualPostgresLockTimeoutIsControlledAndRolledBack(): void
    {
        $registry = self::getContainer()->get(ManagerRegistry::class);
        $blocker = DriverManager::getConnection($registry->getConnection('pgsql')->getParams());
        $blocker->beginTransaction();
        $blocker->executeStatement("UPDATE torture_tasks SET title = 'Uncommitted blocker' WHERE id = 1");
        try {
            $response = $this->requestJsonApi('PATCH', '/api/tasks/1', $this->taskPatch('Blocked writer'), ['X-Torture-Fault' => 'real-lock-timeout']);
            $this->assertJsonApiError($response, 500);
        } finally {
            $blocker->rollBack();
            $blocker->close();
        }
        self::assertSame('Same', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/1'))['data']['attributes']['title']);
    }

    #[Group('torture-gap')]
    #[ExpectedTortureGap('TRANSACTION-BOUNDARY')]
    public function testCrossShardAtomicIsRejectedByBundleBeforeMutation(): void
    {
        $response = $this->atomic([
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Cross-shard first mutation']]],
            ['op' => 'update', 'ref' => ['type' => 'shard-notes', 'id' => 'note-1'], 'data' => ['type' => 'shard-notes', 'attributes' => ['body' => 'Cross-shard second mutation']]],
        ]);
        $first = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'));
        $second = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/shard-notes/note-1'));
        self::assertSame('Primary v2 project 1', $first['data']['attributes']['name']);
        self::assertSame('Tenant B original', $second['data']['attributes']['body']);
        $this->assertJsonApiError($response, 409);
    }

    public function testConcurrentRelationshipAddsPreserveBothIdentifiers(): void
    {
        $base = ['type' => 'tags', 'id' => $this->ids['php']];
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/tasks/1/relationships/labels', ['data' => [$base]]));
        $results = $this->concurrent([
            ['method' => 'POST', 'url' => '/api/tasks/1/relationships/labels', 'body' => ['data' => [['type' => 'tags', 'id' => $this->ids['api']]]]],
            ['method' => 'POST', 'url' => '/api/tasks/1/relationships/labels', 'body' => ['data' => [['type' => 'tags', 'id' => $this->ids['unused-tag']]]]],
        ]);
        self::assertSame([200, 200], array_column($results, 'status'));
        $linkage = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/1/relationships/labels'));
        $this->assertLinkage($linkage['data'], [$base, $this->identifier('api', 'tags'), $this->identifier('unused-tag', 'tags')]);
    }
}
