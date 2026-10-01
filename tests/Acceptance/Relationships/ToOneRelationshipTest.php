<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ToOneRelationshipTest extends AcceptanceTestCase
{
    #[ExpectedBundleGap('RELATIONSHIP-005')]
    public function testReadLinkageAndRelatedResource(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/author'));
        $this->assertResourceIdentifier($doc['data'], 'authors', $this->ids['ada']);
        self::assertArrayHasKey('self', $doc['links']);
        self::assertArrayHasKey('related', $doc['links']);
        $related = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/author'));
        self::assertSame('Ada Lovelace', $related['data']['attributes']['name']);
    }

    public function testReplaceRequiredAuthor(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url().'/relationships/author', ['data' => $this->identifier('grace', 'authors')]));
        $this->assertResourceIdentifier($doc['data'], 'authors', $this->ids['grace']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/author'));
        self::assertSame('Grace Hopper', $after['data']['attributes']['name']);
    }

    public function testClearNullableEditor(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url().'/relationships/editor', ['data' => null]));
        self::assertNull($doc['data']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/editor'));
        self::assertNull($after['data']);
    }

    #[DataProvider('invalidLinkage')]
    public function testInvalidLinkage(string $case, int $status, ?string $pointer): void
    {
        $data = match ($case) {
            'wrong type' => $this->identifier('php', 'tags'),
            'unknown id' => ['type' => 'authors', 'id' => '999999'],
            'missing id' => ['type' => 'authors'],
            'required null' => null,
        };
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url().'/relationships/author', ['data' => $data]), $status, $pointer);
    }

    #[DataProvider('invalidLinkageBundleGaps')]
    #[ExpectedBundleGap('RELATIONSHIP-001', ['#1'])]
    #[ExpectedBundleGap('RELATIONSHIP-004', ['#3'])]
    public function testInvalidLinkageBundleGap(string $case, int $status, ?string $pointer): void
    {
        $data = match ($case) {
            'wrong type' => $this->identifier('php', 'tags'),
            'unknown id' => ['type' => 'authors', 'id' => '999999'],
            'missing id' => ['type' => 'authors'],
            'required null' => null,
        };
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url().'/relationships/author', ['data' => $data]), $status, $pointer);
    }

    private static function invalidLinkageAll(): iterable
    {
        yield ['wrong type', 409, '/data/type'];
        yield ['unknown id', 404, '/data/id'];
        yield ['missing id', 400, '/data/id'];
        yield ['required null', 422, '/data'];
    }

    #[DataProvider('disallowedMethods')]
    public function testToOneMethodRestriction(string $method): void
    {
        $this->assertJsonApiError($this->requestJsonApi($method, $this->url().'/relationships/author', ['data' => $this->identifier('grace', 'authors')]), 405);
    }

    public static function disallowedMethods(): iterable { yield ['POST']; yield ['DELETE']; }

    public function testUnknownParent(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', '/api/articles/999999/relationships/author', ['data' => $this->identifier('ada', 'authors')]), 404);
    }

    public function testUnknownRelationship(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url().'/relationships/unknown'), 404);
    }

    public static function invalidLinkage(): iterable
    {
        foreach (self::invalidLinkageAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (!in_array($label, ['#1', '#3'], true)) { yield $label => $row; }
        }
    }

    public static function invalidLinkageBundleGaps(): iterable
    {
        foreach (self::invalidLinkageAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (in_array($label, ['#1', '#3'], true)) { yield $label => $row; }
        }
    }
}
