<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Doctrine;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class DoctrineConstraintsTest extends AcceptanceTestCase
{
    public function testUniqueConstraintOnCreate(): void
    {
        $response = $this->requestJsonApi('POST', '/api/articles', $this->articlePayload(['slug' => 'article-01']));
        $this->assertJsonApiError($response, 409);
        $doc = $this->collection(['filter' => ['slug' => 'article-01']]);
        self::assertCount(1, $doc['data']);
    }

    public function testUniqueConstraintOnUpdate(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['slug' => 'article-02'])), 409);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('article-01', $doc['data']['attributes']['slug']);
    }

    public function testMissingRelationshipReference(): void
    {
        $payload = $this->articlePayload([], ['author' => ['data' => ['type' => 'authors', 'id' => '999999']]]);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 404, '/data/relationships/author/data/id');
    }

    public function testWrongRelationshipType(): void
    {
        $payload = $this->articlePayload([], ['author' => ['data' => $this->identifier('php', 'tags')]]);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 409, '/data/relationships/author/data/type');
    }

    public function testUnknownRelationship(): void
    {
        $payload = $this->articlePayload([], ['unknown' => ['data' => null]]);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles', $payload), 400, '/data/relationships/unknown');
    }
}
