<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use App\PgEntity\FeatureArticle;
use Doctrine\Persistence\ManagerRegistry;

final class ResourceOptionsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_resource_options'; }
    #[ExpectedBundleGap('RESOURCE-ROUTE-PREFIX')]
    public function testResourceRoutePrefixOverridesGlobalPrefixAndLinks(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Routed article'; $em->persist($article); $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/reference/routed-articles/'.$article->id));
        self::assertSame('Routed article', $doc['data']['attributes']['title']);
        self::assertSame('/reference/routed-articles/'.$article->id, parse_url($doc['data']['links']['self'], PHP_URL_PATH));
        self::assertSame(404, $this->requestJsonApi('GET', '/api/routed-articles/'.$article->id)->getStatusCode());
    }    #[ExpectedBundleGap('DOCS-EXPOSE-ID-CONTRACT')]
    public function testExposeIdFalseDoesNotMakeProtocolIdentityOptionalInDocumentation(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Identity article'; $em->persist($article); $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/identity-articles/'.$article->id));
        self::assertSame((string) $article->id, $doc['data']['id']);
        self::assertArrayNotHasKey('id', $doc['data']['attributes']);
        $response = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => 'application/json']);
        self::assertSame(200, $response->getStatusCode());
        $spec = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $schema = $spec['components']['schemas']['IdentityArticlesResource'];
        $tags = array_column($spec['tags'], null, 'name');
        self::assertSame('Identity remains required by JSON:API', $tags['identity-articles']['description']);
        self::assertContains('id', $schema['required']);
        self::assertFalse($schema['properties']['id']['nullable'] ?? false);
    }
    #[ExpectedBundleGap('RESOURCE-TAG-DISCOVERY')]
    public function testExplicitResourceServiceTagWorksOutsideDiscoveryPaths(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Tagged article'; $em->persist($article); $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tagged-articles/'.$article->id));
        self::assertSame('tagged-articles', $doc['data']['type']);
        self::assertSame('Tagged article', $doc['data']['attributes']['title']);
    }

}
