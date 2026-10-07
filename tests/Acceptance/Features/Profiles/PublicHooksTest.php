<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

use App\JsonApi\Profile\CookbookProfile;
use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class PublicHooksTest extends AcceptanceTestCase
{
    private function headers(): array
    {
        return ['Accept' => self::MEDIA.';profile="'.CookbookProfile::URI.'"'];
    }

    public function testDocumentQueryAndReadHooksCompose(): void
    {
        $response = $this->requestJsonApi('GET', '/api/articles?sort=views&page[size]=20&include=author', headers: $this->headers());
        $doc = $this->decodeJsonApi($response);
        self::assertCount(2, $doc['data']);
        self::assertSame([100, 110], array_column(array_column($doc['data'], 'attributes'), 'views'));
        self::assertTrue($doc['meta']['cookbook_profile']);
        self::assertSame('https://example.test/cookbook', $doc['links']['describedby']);
        self::assertTrue($doc['data'][0]['relationships']['author']['meta']['cookbook']);
        self::assertStringContainsString(CookbookProfile::URI, (string) $response->headers->get('Content-Type'));
        self::assertStringContainsString('Accept', (string) $response->headers->get('Vary'));
    }

    public function testDocumentAndQueryHooksHaveIndependentVisibleEffects(): void
    {
        $response = $this->requestJsonApi('GET', '/api/authors?page[size]=20', headers: $this->headers());
        $doc = $this->decodeJsonApi($response);
        self::assertCount(2, $doc['data']);
        self::assertTrue($doc['meta']['cookbook_profile']);
        self::assertSame('https://example.test/cookbook', $doc['links']['describedby']);
        self::assertTrue($doc['data'][0]['relationships']['articles']['meta']['cookbook']);
    }

    public function testFetchPlanHookRequestsCountsWithoutCountProfile(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url(), headers: $this->headers()));
        self::assertSame(1, $doc['data']['relationships']['author']['meta']['cookbook_count']);
        self::assertSame(2, $doc['data']['relationships']['tags']['meta']['cookbook_count']);
        self::assertArrayNotHasKey('included', $doc);
    }

    public function testWriteHookChangesPersistedRepresentation(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Hook title']), $this->headers()));
        self::assertSame('Profile: Hook title', $doc['data']['attributes']['title']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Profile: Hook title', $doc['data']['attributes']['title']);
    }

    public function testRelationshipHookRejectsBeforePersistence(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url().'/relationships/author', ['data' => $this->identifier('grace', 'authors')], $this->headers()), 403);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/author'));
        self::assertSame($this->ids['ada'], $doc['data']['id']);
    }
    public function testApplicationOwnedFilterFlagAndResourceMetadata(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles?filter[cookbook_one]=true', headers: $this->headers()));
        self::assertCount(1, $doc['data']);
        self::assertSame('articles', $doc['data'][0]['meta']['cookbook_resource']);
        self::assertSame(1, $doc['data'][0]['relationships']['author']['meta']['cookbook_count']);
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?filter[cookbook_one]=true'), 400);
    }    public function testReadItemAndDeleteHooksHaveVisiblePolicyEffects(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Protected profile memo']]]), 201)['data'];
        $url = '/api/feature-memos/'.$created['id'];
        $this->assertJsonApiError($this->requestJsonApi('GET', $url, headers: $this->headers()), 403);
        $this->assertJsonApiError($this->requestJsonApi('DELETE', $url, headers: $this->headers()), 403);
        $this->decodeJsonApi($this->requestJsonApi('GET', $url));
    }
    public function testCreateWriteHookAndLegacyFetchPlanHookAreIndependent(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Hook created article']]], $this->headers()), 201)['data'];
        self::assertSame('Profile: Hook created article', $created['attributes']['title']);
        $author = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/authors/'.$this->ids['grace'], headers: $this->headers()))['data'];
        self::assertArrayHasKey('cookbook_count', $author['relationships']['articles']['meta']);
        self::assertGreaterThan(0, $author['relationships']['articles']['meta']['cookbook_count']);
    }
    #[DataProvider('relationshipChanges')]
    public function testEveryRelationshipWriteHookRejectsBeforePersistence(string $method, string $relationship, array $keys): void
    {
        $url = $this->url().'/relationships/'.$relationship;
        $before = $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data'];
        $targets = array_map(fn (string $key): array => $this->identifier($key, $relationship === 'author' ? 'authors' : 'tags'), $keys);
        $body = ['data' => $relationship === 'author' ? $targets[0] : $targets];
        $this->assertJsonApiError($this->requestJsonApi($method, $url, $body, $this->headers()), 403);
        self::assertSame($before, $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']);
    }
    public static function relationshipChanges(): iterable
    {
        yield 'replace to-one' => ['PATCH', 'author', ['grace']];
        yield 'replace to-many' => ['PATCH', 'tags', ['php']];
        yield 'add to-many' => ['POST', 'tags', ['api']];
        yield 'remove to-many' => ['DELETE', 'tags', ['php']];
    }

}
