<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Composition;

use App\Tests\Acceptance\Support\ProductionTestCase;

final class PublishingQueryTest extends ProductionTestCase
{
    public function testAuthenticatedScopedInheritedSearchAndCustomSortCompose(): void
    {
        $query = ['filter' => ['search' => 'Body', 'author.name' => 'Ada Lovelace', 'tags.name' => 'PHP'], 'sort' => 'title-length,id', 'page' => ['size' => 1, 'number' => 2], 'fields' => ['articles' => 'title,author,tags'], 'include' => 'author,tags'];
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', '/api/articles?'.http_build_query($query)));
        self::assertCount(1, $doc['data']);
        self::assertSame($this->ids['article-1'], $doc['data'][0]['id']);
        self::assertSame(['title'], array_keys($doc['data'][0]['attributes']));
        foreach ($doc['included'] as $resource) {
            if ($resource['type'] === 'authors') { self::assertSame($this->ids['ada'], $resource['id']); }
        }
    }
}
