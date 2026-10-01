<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Query;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class IncludeTest extends AcceptanceTestCase
{
    #[DataProvider('paths')]
    public function testIncludesHaveUniqueFullLinkage(string $include, array $types): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include='.$include));
        self::assertNotEmpty($doc['included']);
        $keys = array_map(static fn (array $r): string => $r['type'].':'.$r['id'], $doc['included']);
        self::assertCount(count(array_unique($keys)), $keys);
        foreach ($types as $type) { self::assertContains($type, array_column($doc['included'], 'type')); }
        // Every included resource must be reachable from primary data through linkage.
        $reachable = [$doc['data']['type'].':'.$doc['data']['id'] => true];
        $all = array_merge([$doc['data']], $doc['included']);
        do {
            $before = count($reachable);
            foreach ($all as $resource) {
                if (!isset($reachable[$resource['type'].':'.$resource['id']])) { continue; }
                foreach ($resource['relationships'] ?? [] as $rel) {
                    $data = $rel['data'] ?? null;
                    foreach ($data === null ? [] : (isset($data['type']) ? [$data] : $data) as $identifier) {
                        $reachable[$identifier['type'].':'.$identifier['id']] = true;
                    }
                }
            }
        } while (count($reachable) > $before);
        foreach ($keys as $key) { self::assertArrayHasKey($key, $reachable); }
    }

    public static function paths(): iterable
    {
        yield ['author', ['authors']];
        yield ['editor', ['authors']];
        yield ['tags', ['tags']];
        yield ['author,editor,tags', ['authors', 'tags']];
        yield ['author.articles', ['authors', 'articles']];
        yield ['author.articles.author', ['authors', 'articles']];
        yield ['tags.articles.author,author.articles.author', ['tags', 'articles', 'authors']];
        yield ['author,author', ['authors']];
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('INCLUDE-001')]
    public function testNullableInclude(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('article-12').'?include=editor'));
        self::assertNull($doc['data']['relationships']['editor']['data']);
        self::assertSame([], $doc['included'] ?? null);
    }

    public function testSelfReferentialInclude(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('root', 'categories').'?include=children.children.parent'));
        $keys = array_map(static fn (array $r): string => $r['type'].':'.$r['id'], $doc['included']);
        self::assertCount(count(array_unique($keys)), $keys);
        self::assertContains('categories:'.$this->ids['leaf'], $keys);
    }
}
