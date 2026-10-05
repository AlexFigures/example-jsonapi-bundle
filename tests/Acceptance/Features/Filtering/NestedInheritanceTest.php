<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\PgEntity\{Article, FeatureArticle};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class NestedInheritanceTest extends AcceptanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Derived article';
        $article->source = $em->find(Article::class, $this->ids['article-1']);
        $em->persist($article); $em->flush();
    }
    #[DataProvider('fields')]
    public function testTwoLevelsOfInheritanceComposeWithInclude(string $field, string $value): void
    {
        $query = ['filter' => [$field => $value], 'sort' => 'source.author.name', 'include' => 'source.author'];
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?'.http_build_query($query)));
        self::assertCount(1, $doc['data']);
        self::assertSame('Derived article', $doc['data'][0]['attributes']['title']);
        self::assertContains('authors', array_column($doc['included'], 'type'));
    }
    public static function fields(): iterable
    {
        yield 'nested name' => ['source.author.name', 'Ada Lovelace'];
        yield 'nested email' => ['source.author.email', 'ada@example.test'];
    }
}
