<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

use App\JsonApi\Profile\CookbookProfile;
use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class PublicHooksTest extends AcceptanceTestCase
{
    private function headers(): array
    {
        return ['Accept' => self::MEDIA.';profile="'.CookbookProfile::URI.'"'];
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('PROFILE-READ-HOOK')]
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

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('PROFILE-RELATIONSHIP-HOOK')]
    public function testRelationshipHookRejectsBeforePersistence(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url().'/relationships/author', ['data' => $this->identifier('grace', 'authors')], $this->headers()), 403);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/author'));
        self::assertSame($this->ids['ada'], $doc['data']['id']);
    }
}
