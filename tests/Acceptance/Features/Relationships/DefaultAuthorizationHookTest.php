<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\PgEntity\Article;
use App\Security\PublishingContext;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

/** A server-default association policy must run without client profile negotiation. */
final class DefaultAuthorizationHookTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_profile_options'; }

    #[DataProvider('mutations')]
    public function testDefaultRelationshipHookRejectsBeforePersistence(string $method, string $relationship, string $target): void
    {
        // Publishing decorators are inactive here: the public bundle hook causes the denial.
        self::assertFalse(self::getContainer()->get(PublishingContext::class)->enabled);
        $type = $relationship === 'author' ? 'authors' : 'tags';
        $identifier = $this->identifier($target, $type);
        $linkage = $relationship === 'author' ? $identifier : [$identifier];
        $this->assertJsonApiError($this->requestJsonApi($method,
            $this->url().'/relationships/'.$relationship, ['data' => $linkage]), 403);
        $manager = self::getContainer()->get(ManagerRegistry::class)->getManager('pgsql');
        $manager->clear();
        $article = $manager->find(Article::class, (int) $this->ids['article-1']);
        self::assertSame($this->ids['ada'], (string) $article->getAuthor()->getId());
        self::assertCount(2, $article->getTags());
    }

    public static function mutations(): iterable
    {
        yield 'replace to-one' => ['PATCH', 'author', 'grace'];
        yield 'replace to-many' => ['PATCH', 'tags', 'unused-tag'];
        yield 'add to-many' => ['POST', 'tags', 'unused-tag'];
        yield 'remove to-many' => ['DELETE', 'tags', 'php'];
    }
}
