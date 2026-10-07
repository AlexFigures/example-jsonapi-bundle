<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Profiles;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ProfileTest extends AcceptanceTestCase
{
    public function testNoProfileHasNoCountMeta(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertArrayNotHasKey('meta', $doc['data']['relationships']['tags']);
    }

    public function testNegotiatedRelationshipCounts(): void
    {
        $response = $this->requestJsonApi('GET', $this->url(), headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"']);
        $doc = $this->decodeJsonApi($response);
        self::assertSame(2, $doc['data']['relationships']['tags']['meta']['count']);
        self::assertStringContainsString('urn:jsonapi:profile:rel-counts', $response->headers->get('Content-Type'));
        self::assertContains('Accept', $response->getVary());
    }

    public function testProfileEnabledByDefaultForTags(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('php', 'tags')));
        self::assertSame(3, $doc['data']['relationships']['articles']['meta']['count'] ?? null);
    }

    public function testUnknownProfileIgnored(): void
    {
        $response = $this->requestJsonApi('GET', $this->url(), headers: ['Accept' => self::MEDIA.';profile="https://example.test/unknown"']);
        $doc = $this->decodeJsonApi($response);
        self::assertArrayNotHasKey('meta', $doc['data']['relationships']['tags']);
        self::assertStringNotContainsString('https://example.test/unknown', $response->headers->get('Content-Type'));
    }
    public function testSoftDeleteProfileExcludesArchivedCategory(): void
    {
        $without = $this->collection(type: 'categories');
        self::assertContains($this->ids['archived'], array_column($without['data'], 'id'));
        $response = $this->requestJsonApi('GET', '/api/categories', headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:soft-delete"']);
        $doc = $this->decodeJsonApi($response);
        self::assertNotContains($this->ids['archived'], array_column($doc['data'], 'id'));
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url('archived', 'categories'), headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:soft-delete"']), 404);
    }

    public function testAuditTrailProfileUpdatesTimestamp(): void
    {
        $response = $this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Audited edit']), ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']);
        $doc = $this->decodeJsonApi($response);
        self::assertGreaterThan(new \DateTimeImmutable('2026-01-15T00:00:00+00:00'), new \DateTimeImmutable($doc['data']['attributes']['updatedAt']));
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame($doc['data']['attributes']['updatedAt'], $after['data']['attributes']['updatedAt']);
    }

}
