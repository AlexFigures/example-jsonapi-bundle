<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Smoke;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class RealWorldWorkflowTest extends AcceptanceTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('WRITE-MODEL-SERIALIZER-METADATA')]
    public function testPublishingWorkflow(): void
    {
        $create = function (string $type, array $attributes): array {
            return $this->decodeJsonApi($this->requestJsonApi('POST', '/api/'.$type, ['data' => ['type' => $type, 'attributes' => $attributes]]), 201)['data'];
        };
        $author = $create('authors', ['name' => 'Workflow Author']);
        $editor = $create('authors', ['name' => 'Workflow Editor']);
        $tag = $create('tags', ['name' => 'Workflow Tag']);
        $identifier = static fn (array $r): array => ['type' => $r['type'], 'id' => $r['id']];
        $payload = $this->articlePayload(['title' => 'Workflow article', 'slug' => 'workflow-article', 'status' => 'published'], [
            'author' => ['data' => $identifier($author)], 'editor' => ['data' => $identifier($editor)], 'tags' => ['data' => [$identifier($tag)]],
        ]);
        $article = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/articles', $payload), 201)['data'];
        $url = '/api/articles/'.$article['id'];
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $url.'?include=author,editor,tags&fields[articles]=title,author,editor,tags&fields[authors]=name'));
        self::assertCount(3, $doc['included']);
        for ($i = 1; $i <= 2; ++$i) {
            $copy = $payload;
            $copy['data']['attributes']['slug'] = 'workflow-extra-'.$i;
            $this->decodeJsonApi($this->requestJsonApi('POST', '/api/articles', $copy), 201);
        }
        $list = $this->collection(['filter' => ['slug' => ['like' => 'workflow%']], 'sort' => 'title,id', 'page' => ['size' => 2], 'fields' => ['articles' => 'title,author'], 'include' => 'author']);
        self::assertCount(2, $list['data']);
        $patch = ['data' => ['type' => 'articles', 'id' => $article['id'], 'attributes' => ['title' => 'Published workflow']]];
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, $patch));
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $url.'/relationships/author', ['data' => $identifier($editor)]));
        $this->decodeJsonApi($this->requestJsonApi('POST', $url.'/relationships/tags', ['data' => [$this->identifier('php', 'tags')]]));
        $this->decodeJsonApi($this->requestJsonApi('DELETE', $url.'/relationships/tags', ['data' => [$identifier($tag)]]));
        $patch['data']['attributes']['title'] = 'Atomically published';
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'ref' => $identifier($article), 'data' => $patch['data']]]));
        $comment = $create('comments', ['body' => 'Workflow comment', 'articleId' => (int) $article['id'], 'authorName' => 'Reader']);
        $readComment = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/comments/'.$comment['id']));
        self::assertSame((int) $article['id'], $readComment['data']['attributes']['articleId']);
        $etag = $this->requestJsonApi('GET', $url)->headers->get('ETag');
        self::assertNotEmpty($etag);
        self::assertSame(304, $this->requestJsonApi('GET', $url, headers: ['If-None-Match' => $etag])->getStatusCode());
        self::assertSame(204, $this->requestJsonApi('DELETE', $url)->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', $url), 404);
    }
}
