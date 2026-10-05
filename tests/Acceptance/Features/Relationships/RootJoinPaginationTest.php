<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\PgEntity\{Author, FeatureArticle, FeatureArticleTag, Tag};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Torture\Infrastructure\RequestMetrics;
use Doctrine\Persistence\ManagerRegistry;

final class RootJoinPaginationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features'; }

    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        for ($i = 1; $i <= 6; ++$i) {
            $article = new FeatureArticle();
            $article->title = 'Root '.$i;
            $article->author = $em->find(Author::class, $this->ids[$i <= 3 ? 'ada' : 'grace']);
            foreach (['php', 'api'] as $tag) {
                $article->articleTags->add(new FeatureArticleTag($article, $em->find(Tag::class, $this->ids[$tag])));
            }
            $em->persist($article);
        }
        $em->flush();
    }

    private function query(int $size, int $page = 1): string
    {
        return '/api/feature-articles?'.http_build_query(['filter' => ['tags.name' => ['in' => ['PHP', 'API']]], 'sort' => 'author.name,id', 'include' => 'tags', 'fields' => ['feature-articles' => 'title,author,tags'], 'page' => ['size' => $size, 'number' => $page]]);
    }

    public function testDistinctRootsAcrossThreePagesOfToManyJoins(): void
    {
        $ids = $titles = [];
        for ($page = 1; $page <= 3; ++$page) {
            $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->query(2, $page)));
            self::assertCount(2, $doc['data']);
            self::assertSame(6, $doc['meta']['total']);
            $ids = array_merge($ids, array_column($doc['data'], 'id'));
            $titles = array_merge($titles, array_column(array_column($doc['data'], 'attributes'), 'title'));
            self::assertCount(2, $doc['included']);
        }
        self::assertCount(6, array_unique($ids));
        self::assertSame(['Root 1', 'Root 2', 'Root 3', 'Root 4', 'Root 5', 'Root 6'], $titles);
    }

    public function testIncludeQueryCountDoesNotGrowPerRoot(): void
    {
        RequestMetrics::start('');
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->query(2)));
        $small = count(RequestMetrics::$queries);
        RequestMetrics::start('');
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->query(6)));
        self::assertCount(6, $doc['data']);
        self::assertGreaterThan(0, $small, 'The SQL instrument must be active.');
        self::assertLessThanOrEqual($small + 2, count(RequestMetrics::$queries), 'Page growth must not introduce a query per root association.');
    }

    protected function tearDown(): void
    {
        RequestMetrics::$active = false;
        parent::tearDown();
    }
}
