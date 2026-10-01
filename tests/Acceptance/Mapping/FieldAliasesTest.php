<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class FieldAliasesTest extends AcceptanceTestCase
{
    public function testReadAndWriteAlias(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['published-at' => '2026-02-01T00:00:00+00:00'])));
        self::assertSame('2026-02-01T00:00:00+00:00', $doc['data']['attributes']['published-at']);
        self::assertArrayNotHasKey('publishedAt', $doc['data']['attributes']);
    }

    public function testSparseAlias(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?fields[articles]=published-at'));
        self::assertSame(['published-at' => null], $doc['data']['attributes']);
    }

    public function testRelationshipAliasReadIncludeAndUrl(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=reviewedBy'));
        self::assertArrayHasKey('reviewedBy', $doc['data']['relationships']);
        self::assertArrayNotHasKey('reviewer', $doc['data']['relationships']);
        self::assertSame('Ada Lovelace', $doc['included'][0]['attributes']['name']);
        $related = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/reviewedBy'));
        $this->assertResourceIdentifier($related['data'], 'authors', $this->ids['ada']);
    }

    public function testRelationshipAliasWrite(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url().'/relationships/reviewedBy', ['data' => $this->identifier('grace', 'authors')]));
        $this->assertResourceIdentifier($doc['data'], 'authors', $this->ids['grace']);
    }

    public function testAliasValidationPointer(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['published-at' => 'not-a-date'])), 422, '/data/attributes/published-at');
    }
}
