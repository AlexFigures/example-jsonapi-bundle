<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class HandlerPriorityTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_priority'; }
    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach (['Alpha', 'A longer title', 'Beta'] as $title) {
            $article = new FeatureArticle(); $article->title = $title; $em->persist($article);
        }
        $em->flush();
    }
    #[DataProvider('queries')]
    public function testHigherPriorityWinsAndUnrelatedFieldsRemainStandard(array $query, array $expected): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?'.http_build_query($query)));
        self::assertSame($expected, array_column(array_column($doc['data'], 'attributes'), 'title'));
    }
    public function testCustomSortTiesRemainStableAcrossPages(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Zulu'; $em->persist($article); $em->flush();
        $titles = [];
        foreach ([1, 2] as $page) {
            $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?sort=priority-sort&page[size]=1&page[number]='.$page));
            $titles[] = $doc['data'][0]['attributes']['title'];
        }
        self::assertSame(['Beta', 'Zulu'], $titles);
    }
    public static function queries(): iterable
    {
        yield 'filter priority' => [['filter' => ['priority-search' => 'ignored']], ['Alpha']];
        yield 'normal field' => [['filter' => ['title' => 'Beta']], ['Beta']];
        yield 'composed filters' => [['filter' => ['priority-search' => 'ignored', 'title' => 'Beta']], []];
        yield 'sort priority asc' => [['sort' => 'priority-sort'], ['Beta', 'Alpha', 'A longer title']];
        yield 'sort priority desc' => [['sort' => '-priority-sort'], ['A longer title', 'Alpha', 'Beta']];
        yield 'duplicate sort field' => [['sort' => 'priority-sort,priority-sort,id'], ['Beta', 'Alpha', 'A longer title']];
        yield 'sort pagination' => [['sort' => 'priority-sort,id', 'page' => ['size' => 1, 'number' => 2]], ['Alpha']];
    }
}
