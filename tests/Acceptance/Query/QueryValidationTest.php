<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class QueryValidationTest extends AcceptanceTestCase
{
    #[DataProvider('invalidQueries')]
    public function testQueryBoundary(string $query, ?string $parameter): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?'.$query), 400, parameter: $parameter);
    }

    #[DataProvider('invalidQueriesBundleGaps')]
    #[ExpectedBundleGap('QUERY-002', ['wrong operator shape'])]
    #[ExpectedBundleGap('ERROR-001', ['unknown filter field'])]
    public function testQueryBoundaryBundleGap(string $query, ?string $parameter): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?'.$query), 400, parameter: $parameter);
    }

    private static function invalidQueriesAll(): iterable
    {
        yield 'unknown include' => ['include=missing', 'include'];
        yield 'nested include' => ['include=author.missing', 'include'];
        yield 'array include' => ['include[]=author', 'include'];
        yield 'unknown fields type' => ['fields[unknown]=name', 'fields[unknown]'];
        yield 'unknown field' => ['fields[articles]=missing', 'fields[articles]'];
        yield 'malformed fields' => ['fields[]=title', 'fields'];
        yield 'array field list' => ['fields[articles][]=title', 'fields[articles]'];
        yield 'unknown sort' => ['sort=missing', 'sort'];
        yield 'unsupported sort' => ['sort=content', 'sort'];
        yield 'minus only' => ['sort=-', 'sort'];
        yield 'array sort' => ['sort[]=title', 'sort'];
        yield 'zero page' => ['page[number]=0', 'page[number]'];
        yield 'negative page' => ['page[number]=-1', 'page[number]'];
        yield 'noninteger page' => ['page[number]=abc', 'page[number]'];
        yield 'nested page number' => ['page[number][x]=1', 'page[number]'];
        yield 'zero size' => ['page[size]=0', 'page[size]'];
        yield 'oversized page' => ['page[size]=21', 'page[size]'];
        yield 'unknown parameter' => ['unknown=1', 'unknown'];
        yield 'unknown filter field' => ['filter[missing]=x', 'filter'];
        yield 'unsupported operator' => ['filter[title][broken]=x', 'filter'];
        yield 'invalid between' => ['filter[views][between][]=1', 'filter'];
        yield 'wrong operator shape' => ['filter[views][gt][x]=1', 'filter'];
    }

    #[DataProvider('validBoundaryQueries')]
    public function testValidBoundarySyntax(string $query): void
    {
        $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles?'.$query));
    }

    public static function validBoundaryQueries(): iterable
    {
        yield ['include=,'];
        yield ['include=author,,author'];
        yield ['fields[articles]'];
        yield ['fields%5Barticles%5D=title'];
        yield ['sort=title&sort=-title'];
    }

    #[ExpectedBundleGap('QUERY-001')]
    public function testMalformedPageListRejected(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?page[]=1'), 400, parameter: 'page');
    }

    #[ExpectedBundleGap('QUERY-001')]
    public function testScalarPageRejected(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?page=1'), 400, parameter: 'page');
    }

    #[ExpectedBundleGap('FILTER-005')]
    public function testEmptyInMatchesNothing(): void
    {
        $doc = $this->collection(['filter' => ['views' => ['in' => '']]]);
        self::assertSame([], $doc['data']);
    }

    #[ExpectedBundleGap('FILTER-005')]
    public function testEmptyNotInMatchesEverything(): void
    {
        $doc = $this->collection(['filter' => ['views' => ['nin' => '']], 'page' => ['size' => 20]]);
        self::assertCount(12, $doc['data']);
    }

    #[ExpectedBundleGap('QUERY-002')]
    public function testWrongIntegerOperandProducesClientError(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?filter[views][gt]=not-an-integer'), 400, parameter: 'filter');
    }
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('QUERY-002')]
    public function testExcessiveFilterDepthIsRejected(): void
    {
        $filter = ['views' => 10];
        for ($i = 0; $i < 40; ++$i) { $filter = ['and' => [$filter]]; }
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?'.http_build_query(['filter' => $filter])), 400, parameter: 'filter');
    }

    #[ExpectedBundleGap('FILTER-002')]
    public function testBetweenRejectsExtraOperand(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?filter[views][between][]=10&filter[views][between][]=20&filter[views][between][]=30'), 400, parameter: 'filter');
    }


    public static function invalidQueries(): iterable
    {
        foreach (self::invalidQueriesAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (!in_array($label, ['unknown filter field', 'wrong operator shape'], true)) { yield $label => $row; }
        }
    }

    public static function invalidQueriesBundleGaps(): iterable
    {
        foreach (self::invalidQueriesAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (in_array($label, ['unknown filter field', 'wrong operator shape'], true)) { yield $label => $row; }
        }
    }
}
