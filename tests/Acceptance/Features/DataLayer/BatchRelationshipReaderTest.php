<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\DataLayer;

use App\PgEntity\{Author, FeatureArticle};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class BatchRelationshipReaderTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_batch'; }
    public function testComputedRelationshipUsesTaggedBatchReaderForLinkageAndInclude(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach (['ada', 'grace'] as $name) {
            $article = new FeatureArticle(); $article->title = $name; $article->author = $em->find(Author::class, $this->ids[$name]); $em->persist($article);
        }
        $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?include=suggestedAuthors&fields[feature-articles]=title,suggestedAuthors&fields[authors]=name'));
        self::assertCount(2, $doc['data']);
        self::assertCount(2, $doc['included']);
        foreach ($doc['data'] as $resource) { self::assertCount(1, $resource['relationships']['suggestedAuthors']['data']); }
        self::assertEqualsCanonicalizing(['Ada Lovelace', 'Grace Hopper'], array_column(array_column($doc['included'], 'attributes'), 'name'));
    }
}
