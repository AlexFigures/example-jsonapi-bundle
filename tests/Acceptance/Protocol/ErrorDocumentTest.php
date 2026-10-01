<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ErrorDocumentTest extends AcceptanceTestCase
{
    public function testMalformedRelationshipObjectPointsToPublicMember(): void
    {
        $payload = $this->articlePayload([], ['author' => []]);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 400, '/data/relationships/author');
    }

    #[ExpectedBundleGap('ERROR-002')]
    public function testMalformedRelationshipDataPointsToData(): void
    {
        $payload = $this->articlePayload([], ['author' => ['data' => []]]);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 400, '/data/relationships/author/data');
    }

    public function testNegotiationErrorsIdentifyHeaders(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles', headers: ['Accept' => 'text/html']), 406, header: 'Accept');
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $this->articlePayload(), ['Content-Type' => 'text/plain']), 415, header: 'Content-Type');
    }

    public function testServerFailureIsSanitized(): void
    {
        // A controlled application exception exercises the bundle's public kernel error boundary.
        $doc = $this->assertJsonApiError($this->requestJsonApi('GET', '/api/acceptance-failure'), 500);
        self::assertStringNotContainsString('acceptance-private-secret', json_encode($doc, JSON_THROW_ON_ERROR));
        self::assertArrayNotHasKey('trace', $doc['errors'][0]['meta'] ?? []);
    }
}
