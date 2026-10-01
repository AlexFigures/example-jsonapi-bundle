<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ToManyRelationshipTest extends AcceptanceTestCase
{
    public function testPopulatedAndEmptyLinkage(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/tags'));
        $this->assertLinkage($doc['data'], [$this->identifier('php', 'tags'), $this->identifier('api', 'tags')]);
        $empty = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('article-12').'/relationships/tags'));
        self::assertSame([], $empty['data']);
        $inverse = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('ada', 'authors').'/relationships/articles?page[size]=20'));
        self::assertCount(8, $inverse['data']);
    }

    #[DataProvider('changes')]
    public function testSetSemantics(string $method, array $keys, array $expected): void
    {
        $rows = array_map(fn (string $key): array => $this->identifier($key, 'tags'), $keys);
        $doc = $this->decodeJsonApi($this->requestJsonApi($method, $this->url().'/relationships/tags', ['data' => $rows]));
        $expectedRows = array_map(fn (string $key): array => $this->identifier($key, 'tags'), $expected);
        $this->assertLinkage($doc['data'], $expectedRows);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/tags'));
        $this->assertLinkage($after['data'], $expectedRows);
    }

    public static function changes(): iterable
    {
        yield 'replace' => ['PATCH', ['unused-tag'], ['unused-tag']];
        yield 'clear' => ['PATCH', [], []];
        yield 'add one' => ['POST', ['unused-tag'], ['php','api','unused-tag']];
        yield 'add several' => ['POST', ['unused-tag','php'], ['php','api','unused-tag']];
        yield 'add existing' => ['POST', ['php'], ['php','api']];
        yield 'duplicate identifiers' => ['POST', ['php','php'], ['php','api']];
        yield 'remove one' => ['DELETE', ['php'], ['api']];
        yield 'remove several' => ['DELETE', ['php','api'], []];
        yield 'remove not linked' => ['DELETE', ['unused-tag'], ['php','api']];
    }

    public function testUnknownRelatedId(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', $this->url().'/relationships/tags', ['data' => [['type' => 'tags', 'id' => '999999']]]), 404);
    }

    public function testWrongRelatedType(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', $this->url().'/relationships/tags', ['data' => [$this->identifier('ada', 'authors')]]), 409);
    }

    public function testUnknownParent(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/articles/999999/relationships/tags', ['data' => [$this->identifier('php', 'tags')]]), 404);
    }
}
