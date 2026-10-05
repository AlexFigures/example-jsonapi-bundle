<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;

final class ConcurrencyAndAtomicTest extends ProductionTestCase
{
    public function testTwoEditorsWithSameEtagOnlyFirstCanPersist(): void
    {
        $read = $this->asUser('editor-a', 'GET', $this->url());
        $etag = $read->headers->get('ETag');
        self::assertNotEmpty($etag);
        $first = $this->decodeJsonApi($this->asUser('editor-a', 'PATCH', $this->url(), $this->patchPayload(['title' => 'Client one']), ['If-Match' => $etag]));
        self::assertSame('Client one', $first['data']['attributes']['title']);
        $this->assertJsonApiError($this->asUser('editor-a', 'PATCH', $this->url(), $this->patchPayload(['title' => 'Client two']), ['If-Match' => $etag]), 412, header: 'If-Match');
        self::assertSame('Client one', $this->storedArticle()->getTitle());
    }

    public function testAtomicDoesNotInventPublishOperation(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'publish', 'ref' => $this->identifier('article-1', 'articles')]], ['Authorization' => 'Bearer editor-a']), 400);
        self::assertSame('draft', $this->storedArticle()->getStatus()->value);
        self::assertSame(0, $this->notificationCount());
    }

    public function testAtomicHrefCannotInvokeCustomCommand(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'href' => $this->url().'/publish', 'data' => $this->patchPayload(['title' => 'Unsupported action'])['data']]], ['Authorization' => 'Bearer editor-a']), 400);
        self::assertSame('draft', $this->storedArticle()->getStatus()->value);
        self::assertSame('Shared title', $this->storedArticle()->getTitle());
        self::assertSame(0, $this->notificationCount());
    }
    public function testOverlappingIfMatchWritesHaveExactlyOneWinner(): void
    {
        $etag = $this->asUser('editor-a', 'GET', $this->url())->headers->get('ETag');
        self::assertNotEmpty($etag);
        $connection = self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class)->getConnection('pgsql');
        // Two independent HTTP kernels/DB connections; hold the first UPDATE open briefly.
        $connection->executeStatement("CREATE FUNCTION delay_article_write() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN PERFORM pg_sleep(1); RETURN NEW; END \$\$");
        $connection->executeStatement('CREATE TRIGGER delay_article_write BEFORE UPDATE ON articles FOR EACH ROW EXECUTE FUNCTION delay_article_write()');
        try {
            $results = \App\Tests\Acceptance\Support\ConcurrentPublishingRequests::run([
                ['method' => 'PATCH', 'url' => $this->url(), 'headers' => ['Authorization' => 'Bearer editor-a', 'If-Match' => $etag], 'body' => $this->patchPayload(['title' => 'Concurrent editor one'])],
                ['method' => 'PATCH', 'url' => $this->url(), 'headers' => ['Authorization' => 'Bearer editor-a', 'If-Match' => $etag], 'body' => $this->patchPayload(['title' => 'Concurrent editor two'])],
            ]);
            foreach ($results as $row) {
                $this->recordHttpResponse('PATCH', $this->url(), $row['status']);
                self::assertStringStartsWith(self::MEDIA, $row['content_type']);
            }
            $statuses = array_column($results, 'status');
            sort($statuses);
            self::assertSame([200, 412], $statuses, 'A pre-mutation ETag check alone does not serialize writes.');
            $winner = array_values(array_filter($results, static fn (array $row): bool => $row['status'] === 200))[0];
            self::assertSame($winner['body']['data']['attributes']['title'], $this->storedArticle()->getTitle());
        } finally {
            $connection->executeStatement('DROP FUNCTION delay_article_write() CASCADE');
        }
    }

}
