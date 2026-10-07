<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\DataLayer;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

final class CustomProviderTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features_memory';
    }

    public function testIndexAndShowWithoutDoctrineEntity(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles'));
        self::assertCount(2, $doc['data']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles/one'));
        self::assertSame('In-memory article', $doc['data']['attributes']['title']);
    }

    public function testCreateWithoutDoctrineEntity(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/memory-articles', ['data' => ['type' => 'memory-articles', 'attributes' => ['title' => 'Application source']]]), 201);
        self::assertSame('Application source', $doc['data']['attributes']['title']);
    }

    public function testPatchWithoutDoctrineEntity(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/memory-articles/one', ['data' => ['type' => 'memory-articles', 'id' => 'one', 'attributes' => ['title' => 'Updated source']]]));
        self::assertSame('Updated source', $doc['data']['attributes']['title']);
    }

    public function testRelatedAndLinkageWithoutDoctrineEntity(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles/one/related'));
        self::assertSame('two', $doc['data']['id']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles/one/relationships/related'));
        self::assertSame('two', $doc['data']['id']);
    }
    public function testPaginationAndDeleteUseTheCustomSource(): void
    {
        $this->client->disableReboot();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles?page[size]=1&page[number]=2'));
        self::assertSame(['two'], array_column($doc['data'], 'id'));
        self::assertSame(204, $this->requestJsonApi('DELETE', '/api/memory-articles/one')->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/memory-articles/one'), 404);
    }
    public function testAtomicBusinessFailureRollsBackEarlierCustomProviderMutation(): void
    {
        $this->client->disableReboot();
        $operation = static fn (string $id, string $title): array => ['op' => 'update', 'ref' => ['type' => 'memory-articles', 'id' => $id], 'data' => ['type' => 'memory-articles', 'id' => $id, 'attributes' => ['title' => $title]]];
        $this->assertJsonApiError($this->atomic([$operation('one', 'Must roll back'), $operation('two', '')]), 422);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles/one'));
        self::assertSame('In-memory article', $doc['data']['attributes']['title']);
    }
    public function testApplicationWriteTransactionCapabilityAndPreloaderAreUsed(): void
    {
        $response = $this->requestJsonApi('POST', '/api/memory-articles', ['data' => ['type' => 'memory-articles', 'attributes' => ['title' => 'Scoped adapter write']]]);
        $this->decodeJsonApi($response, 201);
        self::assertSame([['memory-articles', \App\FeatureMemory\MemoryArticle::class]], json_decode($response->headers->get('X-Example-Write-Scopes'), true, 512, JSON_THROW_ON_ERROR));
        $response = $this->requestJsonApi('GET', '/api/memory-articles/one?include=related');
        $doc = $this->decodeJsonApi($response);
        self::assertSame('two', $doc['data']['relationships']['related']['data']['id']);
        self::assertSame('two', $doc['included'][0]['id']);
        self::assertGreaterThanOrEqual(1, (int) $response->headers->get('X-Example-Preloads'));
    }

}
