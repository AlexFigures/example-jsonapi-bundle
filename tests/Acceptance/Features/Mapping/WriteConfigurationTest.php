<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class WriteConfigurationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_relationship_writes_off'; }

    public function testRelationshipAttributesAreRejectedWhenRelationshipWritesAreDisabled(): void
    {
        $payload = $this->articlePayload();
        $response = $this->requestJsonApi('POST', '/api/articles', $payload);
        $this->assertJsonApiError($response, 400, '/data/relationships');
        self::assertSame(12, (int) self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class)->getConnection('pgsql')->fetchOne('SELECT COUNT(*) FROM articles'));
    }
}
