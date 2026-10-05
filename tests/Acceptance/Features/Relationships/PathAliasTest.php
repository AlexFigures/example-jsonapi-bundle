<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\PgEntity\{Author, FeatureArticle, FeatureArticleTag, Tag};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class PathAliasTest extends AcceptanceTestCase
{
    private string $articleId;

    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle();
        $article->title = 'Join association';
        $article->author = $em->find(Author::class, $this->ids['ada']);
        $article->articleTags->add(new FeatureArticleTag($article, $em->find(Tag::class, $this->ids['php']), 1));
        $article->articleTags->add(new FeatureArticleTag($article, $em->find(Tag::class, $this->ids['api']), 2));
        $em->persist($article);
        $em->flush();
        $this->articleId = (string) $article->id;
    }

    public function testInheritedFilterTraversesUnexposedJoinEntity(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?'.http_build_query(['filter' => ['tags.name' => 'PHP'], 'include' => 'tags'])));
        self::assertCount(1, $doc['data']);
        self::assertCount(2, $doc['included']);
        self::assertSame(['tags'], array_values(array_unique(array_column($doc['included'], 'type'))));
    }

    public function testSerializationIncludesLinkageWithoutExposingJoinState(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/'.$this->articleId.'?include=tags'));
        self::assertCount(2, $doc['data']['relationships']['tags']['data']);
        self::assertArrayNotHasKey('articleTags', $doc['data']['relationships']);
        self::assertArrayNotHasKey('position', $doc['data']['attributes']);
    }

    public function testRelatedEndpointUsesPublicTargets(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/'.$this->articleId.'/tags'));
        self::assertCount(2, $doc['data']);
        self::assertSame(['tags', 'tags'], array_column($doc['data'], 'type'));
    }

    public function testLinkageEndpointUsesPublicIdentifiers(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/'.$this->articleId.'/relationships/tags'));
        $this->assertLinkage($doc['data'], [$this->identifier('php', 'tags'), $this->identifier('api', 'tags')]);
    }

    public function testSparseFieldsetExcludesAlias(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/'.$this->articleId.'?fields[feature-articles]=title'));
        self::assertArrayNotHasKey('tags', $doc['data']['relationships'] ?? []);
    }
}
