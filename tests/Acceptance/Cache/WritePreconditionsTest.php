<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class WritePreconditionsTest extends AcceptanceTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-001')]
    public function testMatchingIfMatchAllowsUpdate(): void
    {
        $etag = $this->requestJsonApi('GET', $this->url())->headers->get('ETag');
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Concurrent update']), ['If-Match' => $etag]));
        self::assertSame('Concurrent update', $doc['data']['attributes']['title']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-001')]
    public function testStaleIfMatchRejectsWithoutChangingState(): void
    {
        $response = $this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Must never persist']), ['If-Match' => '"stale"']);
        $this->assertJsonApiError($response, 412, header: 'If-Match');
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Shared title', $doc['data']['attributes']['title']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-001')]
    public function testStaleIfMatchDoesNotDelete(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('DELETE', $this->url(), headers: ['If-Match' => '"stale"']), 412, header: 'If-Match');
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
    }

    public function testWildcardIfMatch(): void
    {
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Wildcard update']), ['If-Match' => '*']));
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CACHE-001')]
    public function testRequiredPrecondition(): void
    {
        // Activate the bundle's public configuration in a fresh kernel.
        self::ensureKernelShutdown();
        $this->client = static::createClient(['environment' => 'preconditions', 'debug' => false]);
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Requires precondition'])), 428, header: 'If-Match');
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Shared title', $doc['data']['attributes']['title']);
    }
}
