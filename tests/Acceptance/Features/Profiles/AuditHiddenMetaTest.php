<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class AuditHiddenMetaTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_audit_hidden'; }

    public function testConfiguredDisabledExposureDoesNotPublishAuditMeta(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-memos', ['data' => ['type' => 'feature-memos', 'attributes' => ['title' => 'Hidden audit metadata']]], ['Authorization' => 'Bearer editor-a', 'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:audit-trail"']), 201);
        self::assertArrayNotHasKey('meta', $doc['data']);
    }
}
