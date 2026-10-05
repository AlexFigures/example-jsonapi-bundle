<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\DataLayer;

use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class UnplannedRelationshipTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_unplanned'; }
    private function fixture(): string
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Computed fixture'; $em->persist($article); $em->flush();
        return '/api/feature-articles/'.$article->id;
    }
    public function testRejectPolicyDoesNotInvokeUnboundedComputedFallback(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->fixture().'?include=suggestedAuthors'), 400);
    }
    public function testSparseFieldsAvoidUnneededComputedRelationship(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->fixture().'?fields[feature-articles]=title'));
        self::assertSame(['title' => 'Computed fixture'], $doc['data']['attributes']);
        self::assertArrayNotHasKey('relationships', $doc['data']);
    }
}
