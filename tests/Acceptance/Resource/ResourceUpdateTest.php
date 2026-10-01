<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Resource;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ResourceUpdateTest extends AcceptanceTestCase
{
    public function testPartialUpdatePreservesOmittedFields(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Changed title'])));
        self::assertSame('Changed title', $doc['data']['attributes']['title']);
        self::assertSame('Body 1', $doc['data']['attributes']['content']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Changed title', $after['data']['attributes']['title']);
    }

    public function testAttributesAndRelationshipsTogether(): void
    {
        $payload = $this->patchPayload(['title' => 'Edited article', 'content' => 'Edited body', 'published-at' => null]);
        $payload['data']['relationships'] = ['editor' => ['data' => null], 'author' => ['data' => $this->identifier('grace', 'authors')]];
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $payload));
        self::assertNull($doc['data']['relationships']['editor']['data']);
        self::assertNull($doc['data']['attributes']['published-at']);
        self::assertSame($this->ids['grace'], $doc['data']['relationships']['author']['data']['id']);
    }

    #[ExpectedBundleGap('WRITE-001')]
    public function testEmptyAttributeObjectIsValid(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(new \stdClass())));
        self::assertSame('Shared title', $doc['data']['attributes']['title']);
    }

    #[DataProvider('invalidUpdates')]
    public function testInvalidUpdate(string $case, int $status, string $pointer): void
    {
        $payload = $this->patchPayload(['title' => 'Valid title']);
        match ($case) {
            'missing id' => $payload['data']['id'] = null,
            'id mismatch' => $payload['data']['id'] = '999999',
            'type mismatch' => $payload['data']['type'] = 'authors',
            'unknown attribute' => $payload['data']['attributes'] = ['unknown' => 'x'],
            'invalid enum' => $payload['data']['attributes'] = ['status' => 'wrong'],
            'invalid title' => $payload['data']['attributes'] = ['title' => 'x'],
            'readonly' => $payload['data']['attributes'] = ['createdAt' => '1990-01-01T00:00:00Z'],
        };
        if ($case === 'missing id') { unset($payload['data']['id']); }
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $payload), $status, $pointer);
    }

    public static function invalidUpdates(): iterable
    {
        yield ['missing id', 409, '/data/id'];
        yield ['id mismatch', 409, '/data/id'];
        yield ['type mismatch', 409, '/data/type'];
        yield ['unknown attribute', 400, '/data/attributes/unknown'];
        yield ['invalid enum', 422, '/data/attributes/status'];
        yield ['invalid title', 422, '/data/attributes/title'];
        yield ['readonly', 422, '/data/attributes/createdAt'];
    }

    public function testUnknownResource(): void
    {
        $payload = $this->patchPayload(['title' => 'Valid']);
        $payload['data']['id'] = '999999';
        $this->assertJsonApiError($this->requestJsonApi('PATCH', '/api/articles/999999', $payload), 404);
    }
    #[ExpectedBundleGap('RELATIONSHIP-001')]
    public function testUnknownRelatedResourceInPatch(): void
    {
        $payload = $this->patchPayload(['title' => 'Still valid']);
        $payload['data']['relationships'] = ['editor' => ['data' => ['type' => 'authors', 'id' => '999999']]];
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $payload), 404, '/data/relationships/editor/data/id');
    }

    #[ExpectedBundleGap('RELATIONSHIP-002')]
    public function testWrongRelatedTypeInPatch(): void
    {
        $payload = $this->patchPayload(['title' => 'Still valid']);
        $payload['data']['relationships'] = ['editor' => ['data' => $this->identifier('php', 'tags')]];
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $payload), 409, '/data/relationships/editor/data/type');
    }

}
