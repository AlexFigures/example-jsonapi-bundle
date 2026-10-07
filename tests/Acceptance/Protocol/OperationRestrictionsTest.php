<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class OperationRestrictionsTest extends AcceptanceTestCase
{
    public function testReadonlyResourcesCanBeRead(): void
    {
        $doc = $this->collection(type: 'audit-logs');
        self::assertCount(1, $doc['data']);
        $single = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('audit', 'audit-logs')));
        self::assertSame('Article imported', $single['data']['attributes']['message']);
    }

    #[DataProvider('writes')]
    public function testReadonlyWritesAreMethodNotAllowed(string $method): void
    {
        $url = $method === 'POST' ? '/api/audit-logs' : $this->url('audit', 'audit-logs');
        $this->assertJsonApiError($this->requestJsonApi($method, $url, ['data' => ['type' => 'audit-logs', 'id' => $this->ids['audit'], 'attributes' => ['message' => 'Changed']]]), 405);
    }

    public static function writes(): iterable { yield ['POST']; yield ['PATCH']; yield ['DELETE']; }

    public function testReadonlyOptions(): void
    {
        foreach (['/api/audit-logs', $this->url('audit', 'audit-logs')] as $url) {
            $response = $this->requestJsonApi('OPTIONS', $url);
            self::assertSame(204, $response->getStatusCode());
            self::assertEqualsCanonicalizing(['GET','HEAD','OPTIONS'], array_map('trim', explode(',', $response->headers->get('Allow'))));
        }
    }
}
