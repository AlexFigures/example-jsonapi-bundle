<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class EndpointTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_atomic_endpoint'; }
    public function testConfiguredEndpointExecutesAndDocumentationMatchesApplicationRoute(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']]]]), 404);
        $response = $this->requestJsonApi('POST', '/api/batch', ['atomic:operations' => [['op' => 'update', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']], 'data' => $this->patchPayload(['title' => 'Configured batch'])['data']]]], ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC]);
        self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        self::assertSame('Configured batch', $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()))['data']['attributes']['title']);
        $spec = json_decode((string) $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*'])->getContent(), true);
        self::assertArrayHasKey('/api/batch', $spec['paths']);
        self::assertArrayNotHasKey('/api/operations', $spec['paths']);
    }
}
