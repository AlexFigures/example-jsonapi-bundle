<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Sorting;

use App\PgEntity\{FeatureArticle, FeatureArticleTag, Tag};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class AggregateSemanticsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_aggregate'; }

    public function testExplicitAggregateHandlerSupportsToManyTraversal(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach ([['PHP article', 'php'], ['API article', 'api']] as [$title, $tag]) {
            $article = new FeatureArticle();
            $article->title = $title;
            $article->articleTags->add(new FeatureArticleTag($article, $em->find(Tag::class, $this->ids[$tag])));
            $em->persist($article);
        }
        $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?sort=tags.name'));
        self::assertSame(['API article', 'PHP article'], array_column(array_column($doc['data'], 'attributes'), 'title'));
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?sort=-tags.name&page[size]=1&page[number]=2'));
        self::assertSame('API article', $doc['data'][0]['attributes']['title']);
    }
}
