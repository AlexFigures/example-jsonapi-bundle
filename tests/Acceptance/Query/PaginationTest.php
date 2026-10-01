<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class PaginationTest extends AcceptanceTestCase
{
    #[DataProvider('pages')]
    public function testPageBoundaries(int $size, int $number, int $count): void
    {
        $doc = $this->collection(['sort' => 'id', 'page' => ['size' => $size, 'number' => $number]]);
        self::assertCount($count, $doc['data']);
        foreach (['first', 'last'] as $key) { self::assertArrayHasKey($key, $doc['links']); }
        if ($number === 1) { self::assertNull($doc['links']['prev'] ?? null); }
        if ($number * $size >= 12) { self::assertNull($doc['links']['next'] ?? null); }
    }

    public static function pages(): iterable
    {
        yield 'first' => [5,1,5]; yield 'middle' => [5,2,5]; yield 'partial final' => [5,3,2];
        yield 'after final' => [5,4,0]; yield 'one page' => [20,1,12]; yield 'exact boundary' => [6,2,6];
    }

    public function testLinksPreserveQueryShape(): void
    {
        $query = ['sort' => 'views', 'filter' => ['status' => 'published'], 'include' => 'author,tags', 'fields' => ['articles' => 'title,author,tags'], 'page' => ['size' => 2]];
        $doc = $this->collection($query);
        self::assertCount(2, $doc['data']);
        foreach (['self','first','last','next'] as $key) {
            parse_str((string) parse_url($doc['links'][$key], PHP_URL_QUERY), $actual);
            foreach (['sort','filter','include','fields'] as $parameter) { self::assertSame($query[$parameter], $actual[$parameter]); }
        }
        $next = $this->decodeJsonApi($this->requestJsonApi('GET', $doc['links']['next'] ?? null));
        self::assertNotSame(array_column($doc['data'], 'id'), array_column($next['data'], 'id'));
    }

    public function testEmptyFilteredPage(): void
    {
        $doc = $this->collection(['filter' => ['title' => 'absent']]);
        self::assertSame([], $doc['data']);
        self::assertNull($doc['links']['next'] ?? null);
    }
}
