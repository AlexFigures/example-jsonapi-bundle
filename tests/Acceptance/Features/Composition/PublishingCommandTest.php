<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Composition;

use App\Tests\Acceptance\Support\{ProductionTestCase, LifecycleProbe};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class PublishingCommandTest extends ProductionTestCase
{
    public function testBusinessActionAuthorizationCommitEventAndOpenApiCompose(): void
    {
        $this->client->disableReboot();
        $probe = self::getContainer()->get(LifecycleProbe::class);
        $this->assertJsonApiError($this->asUser('reader', 'POST', $this->url().'/publish'), 403);
        self::assertCount(0, $probe->resource);
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/publish'));
        self::assertSame('published', $doc['data']['attributes']['status']);
        self::assertCount(1, $probe->resource);
        self::assertFalse($probe->resource[0]['transaction_active']);
        self::assertSame('published', $probe->resource[0]['committed_status']);
        $response = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*']);
        self::assertSame(200, $response->getStatusCode());
        $spec = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('post', $spec['paths']['/api/articles/{id}/publish']);
    }

    public function testUuidLidRelationshipAndAuthorizationFailureRollBackAtomicBatch(): void
    {
        $id = Uuid::v7()->toRfc4122();
        $operations = [
            ['op' => 'add', 'data' => ['type' => 'newsletters', 'id' => $id, 'attributes' => ['subject' => 'Atomic edition']]],
            ['op' => 'add', 'data' => ['type' => 'newsletters', 'lid' => 'previous', 'attributes' => ['subject' => 'Previous atomic edition']]],
            ['op' => 'update', 'ref' => ['type' => 'newsletters', 'id' => $id, 'relationship' => 'previousEdition'], 'data' => ['type' => 'newsletters', 'lid' => 'previous']],
            ['op' => 'update', 'ref' => ['type' => 'authors', 'id' => $this->ids['grace']], 'data' => ['type' => 'authors', 'id' => $this->ids['grace'], 'attributes' => ['name' => 'Forbidden reassignment']]],
        ];
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', '/api/operations', ['atomic:operations' => $operations], ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC]), 403);
        $db = self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql');
        self::assertSame(0, (int) $db->fetchOne('SELECT COUNT(*) FROM newsletter WHERE id = ?', [$id]));
        self::assertSame('Grace Hopper', $db->fetchOne('SELECT name FROM authors WHERE id = ?', [$this->ids['grace']]));
    }
}
