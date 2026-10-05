<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use App\Tests\Acceptance\Support\ExpectedBundleGap;

final class QueryScopeTest extends ProductionTestCase
{
    public function testScopeComposesWithFilterSortPageIncludeAndFieldsets(): void
    {
        $query = ['filter' => ['search' => 'Body', 'status' => 'draft'], 'sort' => '-title', 'page' => ['size' => 2], 'include' => 'author', 'fields' => ['articles' => 'title,author', 'authors' => 'name']];
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?'.http_build_query($query)));
        self::assertCount(2, $doc['data']);
        self::assertSame([$this->ids['article-1'], $this->ids['article-7']], array_column($doc['data'], 'id'));
        foreach ($doc['data'] as $row) { self::assertSame(['title'], array_keys($row['attributes'])); }
        self::assertSame([$this->ids['ada']], array_column($doc['included'], 'id'));
        $second = $this->decodeJsonApi($this->asUser('editor-a', 'GET', $doc['links']['next']));
        self::assertSame([$this->ids['article-5'], $this->ids['article-3']], array_column($second['data'], 'id'));
    }

    public function testDefaultIndexScopeAndAdminAccess(): void
    {
        $own = $this->decodeJsonApi($this->asUser('editor-b', 'GET', '/api/articles?page[size]=20'));
        self::assertSame(array_map(fn (int $n): string => $this->ids['article-'.$n], range(9, 12)), array_column($own['data'], 'id'));
        $all = $this->decodeJsonApi($this->asUser('admin', 'GET', '/api/articles?page[size]=20'));
        self::assertCount(12, $all['data']);
    }

    public function testClientFilterCannotOverrideScope(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?filter[author.name]=Grace%20Hopper'));
        self::assertSame([], $doc['data']);
    }

    public function testSearchMatchesTitleAndContent(): void
    {
        $title = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?filter[search]=SHARED'));
        self::assertSame([$this->ids['article-1'], $this->ids['article-2']], array_column($title['data'], 'id'));
        $body = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?filter[search]=Body%207'));
        self::assertSame([$this->ids['article-7']], array_column($body['data'], 'id'));
    }

    #[DataProvider('invalidSearch')]
    public function testInvalidSearchInput(string $term): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'GET', '/api/articles?'.http_build_query(['filter' => ['search' => $term]])), 400);
    }
    public static function invalidSearch(): iterable { yield ['']; yield ['ab']; yield [str_repeat('x', 101)]; }

    #[DataProvider('literalTerms')]
    public function testSearchUsesBoundLiteralValues(string $term): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?'.http_build_query(['filter' => ['search' => $term]])));
        self::assertSame([], $doc['data']);
    }
    public static function literalTerms(): iterable { yield ["' OR 1=1 --"]; yield ['%%%']; yield ['___']; }

    public function testProjectionUsesSameOwnershipScope(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-b', 'GET', '/api/article-summaries?page[size]=20'));
        self::assertCount(4, $doc['data']);
        self::assertSame('Article 09', $doc['data'][0]['attributes']['headline']);
        self::assertArrayNotHasKey('content', $doc['data'][0]['attributes']);
        $this->assertJsonApiError($this->asUser('editor-a', 'GET', $this->url('article-9', 'article-summaries')), 403);
    }
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('QUERY-SCOPE-GRAPH')]
    public function testRelatedCollectionCannotLeakForeignArticles(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', $this->url('grace', 'authors').'/articles'));
        self::assertSame([], array_column($doc['data'], 'id'), 'Related collections must honor the same application scope as INDEX.');
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('QUERY-SCOPE-GRAPH')]
    public function testIncludesCannotLeakForeignArticles(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', $this->url('grace', 'authors').'?include=articles'));
        $articles = array_values(array_filter($doc['included'] ?? [], static fn (array $row): bool => $row['type'] === 'articles'));
        self::assertSame([], array_column($articles, 'id'), 'Includes must honor the application-owned Article scope.');
        self::assertSame([], $doc['data']['relationships']['articles']['data'], 'Linkage must not disclose forbidden article identifiers.');
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('QUERY-SCOPE-GRAPH')]
    public function testRelationshipLinkageCannotLeakForeignIdentifiers(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', $this->url('grace', 'authors').'/relationships/articles'));
        self::assertSame([], $doc['data'], 'Linkage must be scoped before pagination, not just existence-checked.');
    }

}
