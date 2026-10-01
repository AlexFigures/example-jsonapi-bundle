<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class SparseFieldsetsTest extends AcceptanceTestCase
{
    public function testOnlyRequestedFields(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?fields[articles]=title'));
        self::assertSame(['title' => 'Shared title'], $doc['data']['attributes']);
        self::assertEmpty($doc['data']['relationships'] ?? []);
    }

    public function testIncludedFieldsetsAndExplicitRelationship(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author&fields[articles]=title,author&fields[authors]=name'));
        self::assertSame(['title'], array_keys($doc['data']['attributes']));
        self::assertSame(['author'], array_keys($doc['data']['relationships']));
        self::assertSame(['name' => 'Ada Lovelace'], $doc['included'][0]['attributes']);
    }

    public function testIncludedRelationshipOmittedFromFieldset(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author&fields[articles]=title'));
        self::assertEmpty($doc['data']['relationships'] ?? []);
        self::assertSame('Ada Lovelace', $doc['included'][0]['attributes']['name']);
    }

    public function testEmptyFieldset(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?fields[articles]='));
        $this->assertResourceIdentifier($doc['data'], 'articles', $this->ids['article-1']);
        self::assertEmpty($doc['data']['attributes'] ?? []);
        self::assertEmpty($doc['data']['relationships'] ?? []);
    }

    public function testIdFieldsetRetainsIdentifierWithoutIdAttribute(): void
    {
        // JSON:API 1.1: "fields" comprises attributes and relationships; id remains an identifier.
        // This application permits id as a selector for an identifier-only representation.
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?fields[articles]=id'));
        $this->assertResourceIdentifier($doc['data'], 'articles', $this->ids['article-1']);
        self::assertEmpty($doc['data']['attributes'] ?? []);
        self::assertEmpty($doc['data']['relationships'] ?? []);
    }
}
