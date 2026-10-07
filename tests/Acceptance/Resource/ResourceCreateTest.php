<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Resource;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ResourceCreateTest extends AcceptanceTestCase
{
    public function testMinimalValidResource(): void
    {
        $response = $this->requestJsonApi('POST', '/api/authors', ['data' => ['type' => 'authors', 'attributes' => ['name' => 'New Author']]]);
        $doc = $this->decodeJsonApi($response, 201);
        $this->assertResourceObject($doc['data'], 'authors');
        self::assertSame($doc['data']['links']['self'], $response->headers->get('Location'));
        $this->decodeJsonApi($this->requestJsonApi('GET', $response->headers->get('Location')));
    }

    public function testAllWritableAttributesAndRelationships(): void
    {
        $payload = $this->articlePayload(['content' => 'Full body', 'status' => 'published', 'metadata' => ['audience' => ['developers']], 'published-at' => '2026-02-01T12:00:00+00:00', 'featured' => true, 'views' => 42, 'rating' => 4.75], ['editor' => ['data' => null], 'tags' => ['data' => [$this->identifier('php', 'tags'), $this->identifier('api', 'tags')]]]);
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/articles', $payload), 201);
        self::assertSame('published', $doc['data']['attributes']['status']);
        self::assertSame(['audience' => ['developers']], $doc['data']['attributes']['metadata']);
        self::assertNull($doc['data']['relationships']['editor']['data']);
        $this->assertLinkage($doc['data']['relationships']['tags']['data'], [$this->identifier('php', 'tags'), $this->identifier('api', 'tags')]);
    }

    #[DataProvider('invalidDocuments')]
    public function testInvalidDocument(string $body, int $status, ?string $pointer): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/authors', $body), $status, $pointer);
    }

    public static function invalidDocuments(): iterable
    {
        yield 'empty body' => ['', 400, null];
        yield 'malformed JSON' => ['{', 400, null];
        yield 'missing data' => ['{"meta":{}}', 400, '/data'];
        yield 'array data' => ['{"data":[]}', 400, '/data'];
        yield 'missing type' => ['{"data":{"attributes":{"name":"Valid"}}}', 400, '/data/type'];
        yield 'empty type' => ['{"data":{"type":""}}', 400, '/data/type'];
        yield 'type mismatch' => ['{"data":{"type":"tags"}}', 409, '/data/type'];
        yield 'attributes scalar' => ['{"data":{"type":"authors","attributes":"bad"}}', 400, '/data/attributes'];
        yield 'unknown attribute' => ['{"data":{"type":"authors","attributes":{"secret":"x"}}}', 400, '/data/attributes/secret'];
        yield 'wrong scalar' => ['{"data":{"type":"authors","attributes":{"name":[]}}}', 422, '/data/attributes/name'];
        yield 'validation' => ['{"data":{"type":"authors","attributes":{"name":"x"}}}', 422, '/data/attributes/name'];
        yield 'email validation' => ['{"data":{"type":"authors","attributes":{"name":"Valid","email":"bad"}}}', 422, '/data/attributes/email'];
    }

    public function testGeneratedClientIdForbidden(): void
    {
        $payload = $this->articlePayload();
        $payload['data']['id'] = 'custom-id';
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 403);
    }

    public function testClientIdAllowedAndCollisionRejected(): void
    {
        $id = 'a0372535-84e4-4d24-b5ad-e4c29b0ad099';
        $payload = ['data' => ['type' => 'subscriptions', 'id' => $id, 'attributes' => ['email' => 'new@example.test']]];
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/subscriptions', $payload), 201);
        $this->assertResourceIdentifier($doc['data'], 'subscriptions', $id);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/subscriptions', $payload), 409);
    }

    public function testRequiredRelationship(): void
    {
        $payload = $this->articlePayload();
        unset($payload['data']['relationships']);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 422, '/data/relationships/author/data');
    }

    public function testInvalidEnum(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $this->articlePayload(['status' => 'broken'])), 422, '/data/attributes/status');
    }

    public function testReadonlyAttributeRejected(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $this->articlePayload(['createdAt' => '1990-01-01T00:00:00Z'])), 422, '/data/attributes/createdAt');
    }
}
