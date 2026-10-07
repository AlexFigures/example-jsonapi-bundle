<?php

declare(strict_types=1);

namespace App\Tests\Torture\Chaos;

use App\Tests\Torture\Support\{ConcurrentRequests, ExpectedTortureGap, TortureTestCase};
use PHPUnit\Framework\Attributes\{Group};

#[Group('chaos')]
final class ConsistencyAndFailureTest extends TortureTestCase
{
    public function testSameWorkerDoesNotLeakIncludeOrTenantState(): void
    {
        foreach ([['X-Tenant-ID' => 'tenant-a'], ['X-Tenant-ID' => 'tenant-b'], ['X-Tenant-ID' => 'tenant-a']] as $headers) {
            $response = $this->workerRequest('GET', '/api/projects/p-1?include=organization', $headers);
            $this->assertJsonApiDocument(json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR));
            self::assertSame([$headers['X-Tenant-ID'] === 'tenant-b' ? 'symfony_pg_torture_b' : 'symfony_pg_torture'], $this->lastMetric()['databases']);
        }
    }

    #[Group('infrastructure-limit')]
    public function testReadReplicaTopologyIsObservableAndLagIsDocumented(): void
    {
        $response = $this->requestJsonApi('GET', '/api/projects/p-1', headers: ['X-Torture-Topology' => 'replica']);
        $document = $this->decodeJsonApi($response);
        self::assertSame('Replica v1 project 1', $document['data']['attributes']['name']);
        self::assertSame(['symfony_pg_torture_replica'], $this->lastMetric()['databases']);
        file_put_contents(dirname(__DIR__, 3).'/var/torture/replica.ndjson', json_encode([
            'operation' => 'GET', 'expected' => 'replica may lag', 'metric' => $this->lastMetric(),
        ], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
    }

    public function testWriteUsesPrimaryAndResponseIsFresh(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/projects', ['data' => ['type' => 'projects', 'id' => 'fresh-project', 'attributes' => ['name' => 'Fresh primary write']]], ['X-Torture-Topology' => 'replica']), 201);
        $this->assertResourceIdentifier($created['data'], 'projects', 'fresh-project');
        self::assertContains('symfony_pg_torture', $this->lastMetric()['databases']);
        self::assertSame('Fresh primary write', $created['data']['attributes']['name']);
    }

    public function testConcurrentIfMatchAllowsOneWriterOnly(): void
    {
        $get = $this->requestJsonApi('GET', '/api/tasks/1');
        $etag = (string) $get->headers->get('ETag');
        self::assertNotSame('', $etag);
        $results = $this->concurrent([
            ['method' => 'PATCH', 'url' => '/api/tasks/1', 'headers' => ['If-Match' => $etag], 'body' => $this->taskPatch('Writer A')],
            ['method' => 'PATCH', 'url' => '/api/tasks/1', 'headers' => ['If-Match' => $etag], 'body' => $this->taskPatch('Writer B')],
        ]);
        $statuses = array_column($results, 'status');
        sort($statuses);
        $winner = array_values(array_filter($results, static fn (array $row): bool => $row['status'] === 200));
        $final = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/1'));
        self::assertSame([200, 412], $statuses);
        self::assertSame($winner[0]['body']['data']['attributes']['title'], $final['data']['attributes']['title']);
    }

    public function testConcurrentUniqueCreateHasOneConflictAndOneRow(): void
    {
        $results = $this->concurrent([
            ['method' => 'POST', 'url' => '/api/projects', 'body' => ['data' => ['type' => 'projects', 'attributes' => ['name' => 'Race project']]]],
            ['method' => 'POST', 'url' => '/api/projects', 'body' => ['data' => ['type' => 'projects', 'attributes' => ['name' => 'Race project']]]],
        ]);
        $statuses = array_map(static fn (array $row): int => $row['status'], $results);
        sort($statuses);
        $rows = $this->collection(['filter' => ['name' => 'Race project']], 'projects');
        self::assertCount(1, $rows['data']);
        self::assertSame([201, 409], $statuses);
    }

    public function testCrossManagerAtomicRejectedBeforeFirstMutation(): void
    {
        $operations = [
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Must not persist']]],
            ['op' => 'update', 'ref' => ['type' => 'comments', 'id' => $this->ids['comment']], 'data' => ['type' => 'comments', 'attributes' => ['body' => 'cross manager']]],
        ];
        $response = $this->atomic($operations);
        self::assertSame('Primary v2 project 1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
        $this->assertJsonApiError($response, 409);
    }

    public function testReplicaFailureIsControlled(): void
    {
        $response = $this->requestJsonApi('GET', '/api/projects/p-1', headers: ['X-Torture-Topology' => 'replica-failure']);
        $document = $this->assertJsonApiError($response, 500);
        self::assertStringNotContainsString('SQLSTATE', json_encode($document, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('symfony', json_encode($document, JSON_THROW_ON_ERROR));
    }

    public function testSingleManagerAtomicDoesNotCommitUnrelatedConnection(): void
    {
        $response = $this->atomic([['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Scoped commit']]]], ['X-Torture-Fault' => 'commit:mysql']);
        $this->decodeJsonApi($response);
        $metric = $this->lastMetric();
        self::assertNotEmpty($metric['transactions']);
        self::assertContains('COMMIT', array_column($metric['transactions'], 'event'));
        foreach ($metric['transactions'] as $transaction) {
            self::assertSame('symfony_pg_torture', $transaction['database'], 'Only the participating connection may be enlisted.');
        }
        self::assertSame('Scoped commit', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
    }
}
