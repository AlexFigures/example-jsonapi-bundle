<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\DataProvider;

final class AuditFieldNamesTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_audit_'.$this->dataName(); }
    #[DataProvider('mappings')]
    public function testCustomAuditFieldNamesTrackCreateAndUpdate(string $mode): void
    {
        $this->client->disableReboot();
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-audit-notes', ['data' => ['type' => 'feature-audit-notes', 'attributes' => ['title' => 'Named audit fields']]], ['Authorization' => 'Bearer editor-a']), 201)['data'];
        self::assertSame('ada@example.test', $created['attributes']['insertedBy']);
        self::assertNotNull($created['attributes']['insertedAt']);
        $id = $created['id'];
        $updated = $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/feature-audit-notes/'.$id, ['data' => ['type' => 'feature-audit-notes', 'id' => $id, 'attributes' => ['title' => 'Modified audit note']]], ['Authorization' => 'Bearer editor-b']))['data'];
        self::assertSame('grace@example.test', $updated['attributes']['modifiedBy']);
        self::assertNotNull($updated['attributes']['modifiedAt']);
        $persisted = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-audit-notes/'.$id))['data'];
        self::assertSame($updated['attributes'], $persisted['attributes']);
    }
    public static function mappings(): iterable { yield 'attribute' => ['attribute']; yield 'config' => ['config']; }
}
