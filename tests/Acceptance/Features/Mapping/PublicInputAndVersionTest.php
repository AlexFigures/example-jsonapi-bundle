<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

use App\PgEntity\{FeatureArticle, FeatureRecord};
use PHPUnit\Framework\Attributes\DataProvider;
use App\JsonApi\Profile\CookbookProfile;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class PublicInputAndVersionTest extends AcceptanceTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('WRITE-REQUEST-DTO')]
    public function testOperationSpecificInputClassIsValidated(): void
    {
        $doc = $this->assertJsonApiError($this->requestJsonApi('POST', '/api/feature-articles', ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'Short']]]), 422, '/data/attributes/title');
        self::assertStringContainsString('20', $doc['errors'][0]['detail']);
    }

    public function testValidOperationSpecificInputCanCreate(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-articles', ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long input title']]]), 201);
        self::assertSame('A sufficiently long input title', $doc['data']['attributes']['title']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('VERSION-RESOLVER-CONTEXT')]
    public function testNegotiatedVersionChangesRepresentation(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle();
        $article->title = 'Original representation';
        $em->persist($article);
        $em->flush();
        $url = '/api/feature-articles/'.$article->id;
        $normal = $this->decodeJsonApi($this->requestJsonApi('GET', $url));
        $version = $this->decodeJsonApi($this->requestJsonApi('GET', $url, headers: ['Accept' => self::MEDIA.';profile="'.CookbookProfile::URI.'"']));
        self::assertSame('Original representation', $normal['data']['attributes']['title']);
        self::assertSame('Alternate representation', $version['data']['attributes']['title']);
        self::assertSame($normal['data']['id'], $version['data']['id']);
    }
    #[DataProvider('representationChannels')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('VERSION-RESOLVER-CONTEXT')]
    public function testVersionSelectionComposesAcrossCollectionsIncludesAndFields(string $channel): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Original representation'; $em->persist($article);
        $record = new FeatureRecord(); $record->title = 'Version parent'; $record->article = $article; $em->persist($record); $em->flush();
        $url = match ($channel) {
            'collection' => '/api/feature-articles',
            'sparse collection' => '/api/feature-articles?fields[feature-articles]=title',
            default => '/api/feature-records/'.$record->id.'?include=article&fields[feature-articles]=title',
        };
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $url, headers: ['Accept' => self::MEDIA.';profile="'.CookbookProfile::URI.'"']));
        $resources = $channel === 'included' ? $doc['included'] : $doc['data'];
        self::assertSame('Alternate representation', $resources[0]['attributes']['title']);
        self::assertSame((string) $article->id, $resources[0]['id']);
    }
    public static function representationChannels(): iterable
    {
        foreach (['collection', 'sparse collection', 'included'] as $channel) { yield $channel => [$channel]; }
    }

}
