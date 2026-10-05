<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Composition;

use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class DtoQueryTest extends AcceptanceTestCase
{
    public function testDtoFilterSortPaginationAndEtagCompose(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach (['Alpha', 'Alpine', 'Beta'] as $title) {
            $article = new FeatureArticle();
            $article->title = $title;
            $em->persist($article);
        }
        $em->flush();
        $url = '/api/feature-summaries?'.http_build_query(['filter' => ['title' => ['like' => 'Al%']], 'sort' => '-title', 'page' => ['size' => 1]]);
        $response = $this->requestJsonApi('GET', $url);
        $doc = $this->decodeJsonApi($response);
        self::assertCount(1, $doc['data']);
        self::assertSame('Alpine', $doc['data'][0]['attributes']['headline']);
        self::assertNotNull($response->headers->get('ETag'));
        self::assertSame(304, $this->requestJsonApi('GET', $url, headers: ['If-None-Match' => $response->headers->get('ETag')])->getStatusCode());
    }
}
