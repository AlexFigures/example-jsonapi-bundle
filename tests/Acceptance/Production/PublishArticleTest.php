<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Enum\ArticleStatus;
use App\Tests\Acceptance\Support\ProductionTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class PublishArticleTest extends ProductionTestCase
{
    public function testEditorJourney(): void
    {
        $list = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?filter[search]=Body&sort=title&page[size]=2&include=author&fields[articles]=title,author'));
        self::assertCount(2, $list['data']);
        self::assertNotEmpty($list['included']);
        $created = $this->decodeJsonApi($this->asUser('editor-a', 'POST', '/api/articles', $this->articlePayload(['content' => 'A production example.'])), 201);
        self::assertSame('draft', $created['data']['attributes']['status']);
        self::assertNull($created['data']['attributes']['published-at']);
        $id = $created['data']['id'];
        $url = '/api/articles/'.$id;
        $this->decodeJsonApi($this->asUser('editor-a', 'PATCH', $url, ['data' => ['type' => 'articles', 'id' => $id, 'attributes' => ['title' => 'Ready for publication']]]));
        $published = $this->decodeJsonApi($this->asUser('editor-a', 'POST', $url.'/publish'));
        self::assertSame('published', $published['data']['attributes']['status']);
        self::assertNotEmpty($published['data']['attributes']['published-at']);
        $this->assertResourceObject($published['data'], 'articles', $id);
        $connection = self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql');
        self::assertSame('published', $connection->fetchOne('SELECT status FROM articles WHERE id = ?', [$id]));
        self::assertSame(1, $this->notificationCount());
        $again = $this->decodeJsonApi($this->asUser('editor-a', 'POST', $url.'/publish'));
        self::assertSame($published['data']['attributes']['published-at'], $again['data']['attributes']['published-at']);
        self::assertSame(1, $this->notificationCount());
    }

    public function testPublicationPersistsRepresentationAndSideEffect(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/publish'));
        self::assertSame('published', $doc['data']['attributes']['status']);
        self::assertSame(ArticleStatus::PUBLISHED, $this->storedArticle()->getStatus());
        self::assertSame($doc['data']['attributes']['published-at'], $this->storedArticle()->getPublishedAt()->format(DATE_ATOM));
        self::assertSame(1, $this->notificationCount());
    }

    #[DataProvider('invalidTransitions')]
    public function testBusinessErrorsHaveNoSideEffect(string $state, bool $emptyContent, int $status): void
    {
        $manager = self::getContainer()->get(ManagerRegistry::class)->getManager('pgsql');
        $article = $manager->find(\App\PgEntity\Article::class, (int) $this->ids['article-1']);
        $article->setStatus(ArticleStatus::from($state));
        if ($emptyContent) { $article->setContent(' '); }
        $manager->flush();
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', $this->url().'/publish'), $status);
        self::assertSame($state, $this->storedArticle()->getStatus()->value);
        self::assertNull($this->storedArticle()->getPublishedAt());
        self::assertSame(0, $this->notificationCount());
    }

    public static function invalidTransitions(): iterable
    {
        yield 'archived' => ['archived', false, 409];
        yield 'missing content' => ['draft', true, 422];
    }

    #[DataProvider('unknownIdentifiers')]
    public function testUnknownOrMalformedArticle(string $id): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', '/api/articles/'.$id.'/publish'), 404);
        self::assertSame(0, $this->notificationCount());
    }

    public static function unknownIdentifiers(): iterable
    {
        yield ['999999']; yield ['not-an-integer']; yield ['9999999999999999999999'];
    }

    public function testRepeatOfPublishedFixtureIsIdempotent(): void
    {
        $before = $this->storedArticle('article-2')->getPublishedAt();
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url('article-2').'/publish'));
        self::assertSame($before->format(DATE_ATOM), $doc['data']['attributes']['published-at']);
        self::assertSame(0, $this->notificationCount());
    }

    public function testCustomActionNegotiatesMediaType(): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', $this->url().'/publish', headers: ['Accept' => 'text/html']), 406);
        self::assertSame(ArticleStatus::DRAFT, $this->storedArticle()->getStatus());
        self::assertSame(0, $this->notificationCount());
    }

    public function testFailedDatabaseWriteRollsBackPublicationAndRecorder(): void
    {
        $probe = self::getContainer()->get(\App\Tests\Acceptance\Support\LifecycleProbe::class);
        $connection = self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql');
        $connection->executeStatement("CREATE FUNCTION reject_publication() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN IF NEW.status = 'published' THEN RAISE EXCEPTION 'Injected publication write failure'; END IF; RETURN NEW; END \$\$");
        $connection->executeStatement('CREATE TRIGGER reject_publication BEFORE UPDATE ON articles FOR EACH ROW EXECUTE FUNCTION reject_publication()');
        try {
            $this->assertJsonApiError($this->asUser('editor-a', 'POST', $this->url().'/publish'), 500);
            self::assertSame(ArticleStatus::DRAFT, $this->storedArticle()->getStatus());
            self::assertNull($this->storedArticle()->getPublishedAt());
            self::assertSame(0, $this->notificationCount());
            self::assertSame([], $probe->resource, 'No successful lifecycle notification after a failed publication.');
        } finally {
            // Schema reset drops the trigger, but functions survive it.
            $connection->executeStatement('DROP FUNCTION reject_publication() CASCADE');
        }
    }
}
