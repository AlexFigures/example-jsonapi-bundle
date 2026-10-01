<?php

declare(strict_types=1);

namespace App\Tests\Torture\Chaos;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('chaos')]
final class ReplicaAndWorkerTest extends TortureTestCase
{
    private const REPLICA = ['X-Torture-Topology' => 'replica'];

    public function testPatchReadsItsWriteWhileIndependentGetCanLag(): void
    {
        $before = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1', headers: self::REPLICA));
        self::assertSame('Replica v1 project 1', $before['data']['attributes']['name']);
        $changed = $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/projects/p-1', ['data' => [
            'type' => 'projects', 'id' => 'p-1', 'attributes' => ['name' => 'Primary v3'],
        ]], self::REPLICA));
        self::assertSame('Primary v3', $changed['data']['attributes']['name']);
        self::assertSame(['symfony_pg_torture'], $this->lastMetric()['databases']);
        $lagged = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1', headers: self::REPLICA));
        self::assertSame('Replica v1 project 1', $lagged['data']['attributes']['name']);
        self::assertSame(['symfony_pg_torture_replica'], $this->lastMetric()['databases']);
        self::assertSame('Primary v3', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
    }

    public function testRelationshipWriteResponseUsesPrimaryState(): void
    {
        $changed = $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/projects/p-1/relationships/owner', [
            'data' => ['type' => 'workspace-users', 'id' => 'u-2'],
        ], self::REPLICA));
        $this->assertResourceIdentifier($changed['data'], 'workspace-users', 'u-2');
        self::assertSame(['symfony_pg_torture'], $this->lastMetric()['databases']);
        self::assertSame('u-1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/relationships/owner', headers: self::REPLICA))['data']['id']);
        self::assertSame('u-2', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/relationships/owner'))['data']['id']);
    }

    public function testAtomicUsesPrimaryAndReturnsFreshResult(): void
    {
        $result = $this->decodeJsonApi($this->atomic([['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'],
            'data' => ['type' => 'projects', 'attributes' => ['name' => 'Atomic primary v3']],
        ]], self::REPLICA));
        self::assertSame('Atomic primary v3', $result['atomic:results'][0]['data']['attributes']['name']);
        self::assertSame(['symfony_pg_torture'], $this->lastMetric()['databases']);
        self::assertNotEmpty($this->lastMetric()['transactions']);
        self::assertSame('Replica v1 project 1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1', headers: self::REPLICA))['data']['attributes']['name']);
    }

    public function testDeleteUsesPrimaryEvenWhenReplicaHasNoCreatedResource(): void
    {
        $this->decodeJsonApi($this->requestJsonApi('POST', '/api/projects', ['data' => ['type' => 'projects', 'id' => 'primary-only', 'attributes' => ['name' => 'Primary only']]], self::REPLICA), 201);
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/projects/primary-only', headers: self::REPLICA), 404);
        self::assertSame(204, $this->requestJsonApi('DELETE', '/api/projects/primary-only', headers: self::REPLICA)->getStatusCode());
        self::assertSame(['symfony_pg_torture'], $this->lastMetric()['databases']);
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/projects/primary-only'), 404);
    }

    #[Group('application-policy')]
    public function testDbalWorkerConnectionRemainsPinnedUntilHostResetsIt(): void
    {
        $this->decodeJsonApi($this->workerRequest('PATCH', '/api/projects/p-1', self::REPLICA, ['data' => ['type' => 'projects', 'id' => 'p-1', 'attributes' => ['name' => 'Worker primary v3']]]));
        $sameWorker = $this->decodeJsonApi($this->workerRequest('GET', '/api/projects/p-1', self::REPLICA));
        self::assertSame('Worker primary v3', $sameWorker['data']['attributes']['name']);
        self::assertNotContains('symfony_pg_torture_replica', $this->lastMetric()['databases']);
        // Closing Doctrine connections/clearing ORM identity maps is a host lifecycle policy.
        static::ensureKernelShutdown();
        $this->client = static::createClient(['environment' => 'torture', 'debug' => false]);
        $newWorker = $this->decodeJsonApi($this->workerRequest('GET', '/api/projects/p-1', self::REPLICA));
        self::assertSame('Replica v1 project 1', $newWorker['data']['attributes']['name']);
    }

    public function testWorkerProfileIncludeCriteriaAndMediaDoNotLeak(): void
    {
        $profile = ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"'];
        $first = $this->decodeJsonApi($this->workerRequest('GET', '/api/tasks/1?include=parent.children&fields[tasks]=title,labels', $profile));
        self::assertSame(5, $first['data']['relationships']['labels']['meta']['count']);
        $badMedia = $this->workerRequest('GET', '/api/tasks/1', ['Accept' => 'text/html']);
        $this->assertJsonApiError($badMedia, 406);
        $normal = $this->decodeJsonApi($this->workerRequest('GET', '/api/tasks/1'));
        self::assertArrayHasKey('description', $normal['data']['attributes']);
        self::assertArrayNotHasKey('meta', $normal['data']['relationships']['labels']);
        self::assertArrayNotHasKey('included', $normal);
        $cycle = $this->decodeJsonApi($this->workerRequest('GET', '/api/tasks/2?include=parent.children.parent'));
        self::assertNotEmpty($cycle['included']);
        self::assertArrayNotHasKey('included', $this->decodeJsonApi($this->workerRequest('GET', '/api/tasks/2')));
    }

    public function testWorkerAtomicLocalIdsAreScopedToOneBatch(): void
    {
        $headers = ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC];
        $add = ['atomic:operations' => [['op' => 'add', 'href' => '/api/projects', 'data' => [
            'type' => 'projects', 'lid' => 'request-local', 'attributes' => ['name' => 'First local project'],
        ]]]];
        $this->decodeJsonApi($this->workerRequest('POST', '/api/operations', $headers, $add, resetAfter: true));
        $unknown = ['atomic:operations' => [['op' => 'update', 'ref' => ['type' => 'projects', 'lid' => 'request-local'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Leaked lid']]]]];
        $this->assertJsonApiError($this->workerRequest('POST', '/api/operations', $headers, $unknown, resetAfter: true), 400);
        $add['atomic:operations'][0]['data']['attributes']['name'] = 'Second local project';
        $this->decodeJsonApi($this->workerRequest('POST', '/api/operations', $headers, $add, resetAfter: true));
    }
}
