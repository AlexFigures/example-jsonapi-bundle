<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

final class AuditIdentityTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_audit'; }
    public function testCreateUpdateTrackAuthenticatedIdentityAndPersistAuditFields(): void
    {
        $response = $this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Audited memo']]], ['Authorization' => 'Bearer editor-a', 'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']);
        $created = $this->decodeJsonApi($response, 201)['data'];
        self::assertSame('ada@example.test', $created['attributes']['createdBy']);
        $url = '/api/feature-memos/'.$created['id'];
        $updated = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, ['data' => ['type' => 'feature-memos', 'id' => $created['id'], 'attributes' => ['title' => 'Updated memo']]], ['Authorization' => 'Bearer editor-b', 'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']))['data'];
        self::assertSame('ada@example.test', $updated['attributes']['createdBy']);
        self::assertSame('grace@example.test', $updated['attributes']['updatedBy']);
        self::assertSame($updated['attributes'], $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['attributes']);
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $url, ['data' => ['type' => 'feature-memos', 'id' => $created['id'], 'attributes' => ['createdBy' => 'forged']]], ['Authorization' => 'Bearer editor-a', 'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']), 422);
    }
    public function testConfiguredAuditMetaIsExposedOnNegotiatedRepresentation(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Audit metadata memo']]], ['Authorization' => 'Bearer editor-a', 'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']), 201);
        self::assertArrayHasKey('meta', $doc['data'], 'expose_in_meta must have an externally visible resource metadata effect.');
        self::assertStringContainsString('ada@example.test', json_encode($doc['data']['meta'], JSON_THROW_ON_ERROR));
    }

    public function testPerTypeDefaultProfileAppliesToWriteHooksWithoutExplicitNegotiation(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Default audit memo']]], ['Authorization' => 'Bearer editor-a']), 201);
        self::assertSame('ada@example.test', $doc['data']['attributes']['createdBy']);
        self::assertNotEmpty($doc['data']['attributes']['createdAt']);
        $id = $doc['data']['id'];
        $updated = $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/feature-memos/'.$id, ['data' => ['type' => 'feature-memos', 'id' => $id, 'attributes' => ['title' => 'Default audit update']]], ['Authorization' => 'Bearer editor-b']))['data'];
        self::assertSame('ada@example.test', $updated['attributes']['createdBy']);
        self::assertSame('grace@example.test', $updated['attributes']['updatedBy']);
        self::assertNotEmpty($updated['attributes']['updatedAt']);
    }

}
