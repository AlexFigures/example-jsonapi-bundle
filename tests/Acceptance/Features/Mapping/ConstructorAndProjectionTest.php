<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class ConstructorAndProjectionTest extends AcceptanceTestCase
{
    public function testRequiredConstructorArgumentAndServerDefault(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Constructor title']]]), 201);
        self::assertSame('Constructor title', $doc['data']['attributes']['title']);
        self::assertSame('draft', $doc['data']['attributes']['state']);
    }

    public function testConstructorValidationErrorPointsAtAttribute(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => '']]]), 422, '/data/attributes/title');
    }

    public function testPublicCustomReadMapperProjectsEntityThroughHttp(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle();
        $article->title = 'Mapped article';
        $em->persist($article);
        $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-custom-summaries/'.$article->id));
        self::assertSame('Custom: Mapped article', $doc['data']['attributes']['headline']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-custom-summaries?sort=title'));
        self::assertSame('Custom: Mapped article', $doc['data'][0]['attributes']['headline']);
    }

    public function testDtoIdentifierNameAndRootOrderingAcrossPages(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach (['Delta', 'Alpha', 'Charlie', 'Bravo'] as $title) {
            $article = new FeatureArticle();
            $article->title = $title;
            $em->persist($article);
        }
        $em->flush();
        $first = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-summaries?sort=title&page[size]=2&page[number]=1'));
        $second = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-summaries?sort=title&page[size]=2&page[number]=2'));
        self::assertSame(['Alpha', 'Bravo', 'Charlie', 'Delta'], array_column(array_column(array_merge($first['data'], $second['data']), 'attributes'), 'headline'));
        self::assertCount(4, array_unique(array_column(array_merge($first['data'], $second['data']), 'id')));
    }
}
