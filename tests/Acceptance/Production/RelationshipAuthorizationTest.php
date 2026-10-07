<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RelationshipAuthorizationTest extends ProductionTestCase
{
    #[DataProvider('mutations')]
    public function testExistingTargetDoesNotGrantPermission(string $method, string $relationship, string $target): void
    {
        $type = $relationship === 'author' ? 'authors' : 'tags';
        $identifier = $this->identifier($target, $type);
        $this->decodeJsonApi($this->asUser('admin', 'GET', $this->url($target, $type)));
        $linkage = $relationship === 'author' ? $identifier : [$identifier];
        $document = $this->assertJsonApiError($this->asUser('reader', $method,
            $this->url().'/relationships/'.$relationship, ['data' => $linkage]), 403);
        self::assertSame('forbidden', $document['errors'][0]['code']);
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getAuthor()->getId());
        self::assertCount(2, $this->storedArticle()->getTags());
    }

    public static function mutations(): iterable
    {
        yield 'replace to-one' => ['PATCH', 'author', 'grace'];
        yield 'replace to-many' => ['PATCH', 'tags', 'unused-tag'];
        yield 'add to-many' => ['POST', 'tags', 'unused-tag'];
        yield 'remove to-many' => ['DELETE', 'tags', 'php'];
    }

    public function testNullLinkageCannotBypassAuthorization(): void
    {
        $this->assertJsonApiError($this->asUser('reader', 'PATCH', $this->url().'/relationships/editor', ['data' => null]), 403);
        self::assertSame($this->ids['grace'], (string) $this->storedArticle()->getEditor()->getId());
    }

    public function testForbiddenRelationshipRollsBackEarlierAtomicMutation(): void
    {
        $original = $this->storedArticle()->getTitle();
        $this->assertJsonApiError($this->atomic([
            ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => [
                'type' => 'articles', 'id' => $this->ids['article-1'], 'attributes' => ['title' => 'Must roll back'],
            ]],
            ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles') + ['relationship' => 'author'],
                'data' => $this->identifier('grace', 'authors')],
        ], ['Authorization' => 'Bearer editor-a']), 403);
        self::assertSame($original, $this->storedArticle()->getTitle());
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getAuthor()->getId());
    }
}
