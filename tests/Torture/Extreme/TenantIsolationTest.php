<?php

declare(strict_types=1);

namespace App\Tests\Torture\Extreme;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('extreme')]
final class TenantIsolationTest extends TortureTestCase
{
    public static function tenants(): array { return ['A' => ['tenant-a', 'Primary v2'], 'B' => ['tenant-b', 'Tenant B']]; }

    #[DataProvider('tenants')]
    public function testShardCrudQueriesAndRelationships(string $tenant, string $name): void
    {
        $headers = ['X-Tenant-ID' => $tenant];
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1?include=organization,owner', headers: $headers));
        self::assertSame($name.' project 1', $doc['data']['attributes']['name']);
        $expectedDatabase = $tenant === 'tenant-b' ? 'symfony_pg_torture_b' : 'symfony_pg_torture';
        self::assertSame([$expectedDatabase], $this->lastMetric()['databases']);
        $listed = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects?'.http_build_query(['filter' => ['name' => ['like' => $name.'%']], 'sort' => 'name', 'page' => ['size' => 1]]), headers: $headers));
        self::assertCount(1, $listed['data']);
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/projects', ['data' => ['type' => 'projects', 'id' => 'new-project', 'attributes' => ['name' => 'Created '.$tenant], 'relationships' => ['owner' => ['data' => ['type' => 'workspace-users', 'id' => 'u-1']]]]], $headers), 201);
        $this->assertResourceIdentifier($created['data'], 'projects', 'new-project');
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/projects/new-project', ['data' => ['type' => 'projects', 'id' => 'new-project', 'attributes' => ['name' => 'Updated '.$tenant]]], $headers));
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/projects/new-project/relationships/owner', ['data' => ['type' => 'workspace-users', 'id' => 'u-2']], $headers));
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/new-project/owner', headers: $headers))['data'], 'workspace-users', 'u-2');
        $other = $tenant === 'tenant-a' ? 'tenant-b' : 'tenant-a';
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/projects/new-project', headers: ['X-Tenant-ID' => $other]), 404);
        self::assertSame(204, $this->requestJsonApi('DELETE', '/api/projects/new-project', headers: $headers)->getStatusCode());
    }

    public function testIdenticalIdsDoNotLeakBetweenTenantsInOneWorker(): void
    {
        foreach ([['tenant-a', 'Primary v2'], ['tenant-b', 'Tenant B'], ['tenant-a', 'Primary v2']] as [$tenant, $name]) {
            $doc = $this->decodeJsonApi($this->workerRequest('GET', '/api/projects/p-1?include=organization', ['X-Tenant-ID' => $tenant]));
            self::assertSame($name.' project 1', $doc['data']['attributes']['name']);
        }
    }

    public function testRelationshipLookupCannotResolveAnotherTenantsIdentifier(): void
    {
        $this->decodeJsonApi($this->requestJsonApi('GET', '/api/workspace-users/only-b', headers: ['X-Tenant-ID' => 'tenant-b']));
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/workspace-users/only-b'), 404);
        $response = $this->requestJsonApi('PATCH', '/api/projects/p-1/relationships/owner', [
            'data' => ['type' => 'workspace-users', 'id' => 'only-b'],
        ]);
        $this->assertJsonApiError($response, 404);
        self::assertSame('u-1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/relationships/owner'))['data']['id']);
    }

    #[Group('application-policy')]
    public function testCrossShardRelationshipIsRejectedBeforePersistence(): void
    {
        $payload = ['data' => ['type' => 'workspace-users', 'id' => 'u-1', 'meta' => ['tenant' => 'tenant-b']]];
        $this->assertJsonApiError($this->requestJsonApi('PATCH', '/api/projects/p-1/relationships/owner', $payload, ['X-Tenant-ID' => 'tenant-a']), 409);
        self::assertSame(0, $this->lastMetric()['query_count']);
        self::assertSame('u-1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1/relationships/owner'))['data']['id']);
    }

    #[Group('application-policy')]
    public function testCrossShardAtomicIsRejectedBeforeFirstMutation(): void
    {
        $operations = [
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Must not persist']]],
            ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'meta' => ['tenant' => 'tenant-b'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Must not persist either']]],
        ];
        $response = $this->atomic($operations, ['X-Tenant-ID' => 'tenant-a']);
        $this->assertJsonApiError($response, 409);
        self::assertSame(0, $this->lastMetric()['query_count']);
        self::assertSame('Primary v2 project 1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
        self::assertSame('Tenant B project 1', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1', headers: ['X-Tenant-ID' => 'tenant-b']))['data']['attributes']['name']);
    }
}
