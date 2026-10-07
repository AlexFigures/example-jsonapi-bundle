<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use Doctrine\Persistence\ManagerRegistry;

final class SoftDeleteActorTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_soft_include'; }

    #[ExpectedBundleGap('PROFILE-SOFT-DELETE-ACTOR-META')]
    public function testConfiguredActorFieldAppearsInNegotiatedSoftDeleteMetadata(): void
    {
        self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql')->executeStatement(
            'UPDATE categories SET removed_by = ? WHERE id = ?', ['editor@example.test', $this->ids['archived']],
        );
        $resource = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/categories/'.$this->ids['archived'], headers: [
            'Accept' => self::MEDIA.';profile="urn:jsonapi:profile:soft-delete"',
        ]))['data'];
        self::assertSame('editor@example.test', $resource['meta']['deletedBy'] ?? null, 'The documented soft-delete metadata must honor deletedByField; actor assignment itself remains application policy.');
        self::assertArrayNotHasKey('removedBy', $resource['attributes']);
    }
}
