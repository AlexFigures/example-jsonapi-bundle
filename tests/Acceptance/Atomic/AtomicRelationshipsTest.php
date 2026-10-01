<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicRelationshipsTest extends AcceptanceTestCase
{
    #[DataProvider('operations')]
    public function testRelationshipMutation(string $op, string $relationship, array $keys, array $expected): void
    {
        $toOne = in_array($relationship, ['author','editor'], true);
        $data = $toOne ? ($keys === [] ? null : $this->identifier($keys[0], 'authors')) : array_map(fn (string $k): array => $this->identifier($k, 'tags'), $keys);
        $operation = ['op' => $op, 'ref' => $this->identifier('article-1', 'articles') + ['relationship' => $relationship], 'data' => $data];
        $response = $this->atomic([$operation]);
        $doc = $this->decodeJsonApi($response);
        self::assertCount(1, $doc['atomic:results']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/'.$relationship));
        if ($toOne) {
            if ($expected === []) { self::assertNull($after['data']); }
            else { $this->assertResourceIdentifier($after['data'], 'authors', $this->ids[$expected[0]]); }
        } else {
            $this->assertLinkage($after['data'], array_map(fn (string $k): array => $this->identifier($k, 'tags'), $expected));
        }
    }

    public static function operations(): iterable
    {
        yield ['update','author',['grace'],['grace']];
        yield ['update','editor',[],[]];
        yield ['update','tags',['unused-tag'],['unused-tag']];
        yield ['update','tags',[],[]];
        yield ['add','tags',['unused-tag'],['php','api','unused-tag']];
        yield ['add','tags',['php','php'],['php','api']];
        yield ['remove','tags',['php'],['api']];
    }

    public function testRelationshipHref(): void
    {
        $response = $this->atomic([['op' => 'update', 'href' => $this->url().'/relationships/editor', 'data' => null]]);
        $doc = $this->decodeJsonApi($response);
        self::assertCount(1, $doc['atomic:results']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/editor'));
        self::assertNull($after['data']);
    }

    #[DataProvider('invalidOperations')]
    public function testInvalidRelationshipOperation(string $case, int $status): void
    {
        $operation = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles') + ['relationship' => 'author'], 'data' => $this->identifier('grace', 'authors')];
        match ($case) {
            'unknown relationship' => $operation['ref']['relationship'] = 'unknown',
            'wrong type' => $operation['data'] = $this->identifier('php', 'tags'),
            'unknown target' => $operation['data']['id'] = '999999',
            'invalid cardinality operation' => $operation['op'] = 'add',
        };
        $this->assertJsonApiError($this->atomic([$operation]), $status);
    }

    public static function invalidOperations(): iterable
    {
        yield ['unknown relationship',400]; yield ['wrong type',400];
        yield ['unknown target',404]; yield ['invalid cardinality operation',400];
    }
}
