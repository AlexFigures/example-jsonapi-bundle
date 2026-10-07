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
    public function testVersionSelectionComposesAcrossCollectionsIncludesAndFields(string $channel): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle(); $article->title = 'Original representation'; $em->persist($article);
        $record = new FeatureRecord(); $record->title = 'Version parent'; $record->article = $article; $em->persist($record); $em->flush();
        $url = match ($channel) {
            'collection' => '/api/feature-articles',
            'sparse collection' => '/api/feature-articles?fields[feature-articles]=title',
            'related' => '/api/feature-records/'.$record->id.'/article',
            default => '/api/feature-records/'.$record->id.'?include=article&fields[feature-articles]=title',
        };
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $url, headers: ['Accept' => self::MEDIA.';profile="'.CookbookProfile::URI.'"']));
        $resources = match ($channel) { 'included' => $doc['included'], 'related' => [$doc['data']], default => $doc['data'] };
        $normal = $this->decodeJsonApi($this->requestJsonApi('GET', $url));
        $defaultResources = match ($channel) { 'included' => $normal['included'], 'related' => [$normal['data']], default => $normal['data'] };
        self::assertSame('Original representation', $defaultResources[0]['attributes']['title']);
        self::assertSame($defaultResources[0]['id'], $resources[0]['id']);
        self::assertSame('Alternate representation', $resources[0]['attributes']['title']);
        self::assertSame((string) $article->id, $resources[0]['id']);
    }
    public static function representationChannels(): iterable
    {
        foreach (['collection', 'sparse collection', 'included', 'related'] as $channel) { yield $channel => [$channel]; }
    }

    public function testUpdateUsesIndependentInputAndPreservesPartialPatch(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-articles', ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long input title']]]), 201)['data'];
        $url = '/api/feature-articles/'.$created['id'];
        $data = ['type' => 'feature-articles', 'id' => $created['id'], 'attributes' => ['title' => 'Shorter']];
        $updated = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, ['data' => $data]))['data'];
        self::assertSame('Shorter', $updated['attributes']['title']);
        $data['attributes'] = new \stdClass();
        $partial = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, ['data' => $data]))['data'];
        self::assertSame('Shorter', $partial['attributes']['title']);
        $data['attributes'] = ['title' => 'Tiny'];
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $url, ['data' => $data]), 422, '/data/attributes/title');
        self::assertSame('Shorter', $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['attributes']['title']);
    }

    #[DataProvider('invalidInputFields')]
    public function testInputRejectsUnknownAndServerOwnedFields(string $field): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/feature-articles', ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long input title', $field => 'forged']]]), 400, '/data/attributes/'.$field);
    }

    public static function invalidInputFields(): iterable
    {
        yield ['unknown'];
        yield ['subtitle'];
    }
    public function testCreateInputAcceptsConfiguredRelationship(): void
    {
        $payload = ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long relationship input'], 'relationships' => ['author' => ['data' => $this->identifier('ada', 'authors')]]]];
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-articles', $payload), 201)['data'];
        self::assertSame($this->ids['ada'], $created['relationships']['author']['data']['id']);
    }

    public function testAtomicSelectsCreateAndUpdateInputsAndRollsBackValidationFailure(): void
    {
        $created = $this->decodeJsonApi($this->atomic([['op' => 'add', 'data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long Atomic input']]]]))['atomic:results'][0]['data'];
        $operation = static fn (string $title): array => ['op' => 'update', 'ref' => ['type' => 'feature-articles', 'id' => $created['id']], 'data' => ['type' => 'feature-articles', 'id' => $created['id'], 'attributes' => ['title' => $title]]];
        $doc = $this->decodeJsonApi($this->atomic([$operation('Shorter')]));
        self::assertSame('Shorter', $doc['atomic:results'][0]['data']['attributes']['title']);
        $error = $this->assertJsonApiError($this->atomic([$operation('Must roll back'), $operation('Tiny')]), 422);
        self::assertSame('/atomic:operations/1/data/attributes/title', $error['errors'][0]['source']['pointer']);
        self::assertSame('Shorter', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/'.$created['id']))['data']['attributes']['title']);
        $this->assertJsonApiError($this->atomic([['op' => 'add', 'data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'Short']]]]), 422);
    }
}
