<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RelationshipPolicyTest extends ProductionTestCase
{
    public function testEditorMayAddAndRemoveTags(): void
    {
        $target = $this->identifier('unused-tag', 'tags');
        $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/relationships/tags', ['data' => [$target]]));
        self::assertCount(3, $this->storedArticle()->getTags());
        $this->decodeJsonApi($this->asUser('editor-a', 'DELETE', $this->url().'/relationships/tags', ['data' => [$target]]));
        self::assertCount(2, $this->storedArticle()->getTags());
    }

    #[DataProvider('forbiddenAssociations')]
    public function testRelationshipPolicy(string $user, string $method, string $relationship, string $targetKey, string $targetType): void
    {
        $target = $this->identifier($targetKey, $targetType);
        $data = $relationship === 'tags' ? [$target] : $target;
        $this->assertJsonApiError($this->asUser($user, $method, $this->url().'/relationships/'.$relationship, ['data' => $data]), 403);
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getAuthor()->getId());
        self::assertSame($this->ids['grace'], (string) $this->storedArticle()->getEditor()->getId());
        self::assertCount(2, $this->storedArticle()->getTags());
    }
    public static function forbiddenAssociations(): iterable
    {
        yield 'editor cannot change author' => ['editor-a', 'PATCH', 'author', 'grace', 'authors'];
        yield 'editor cannot assign another editor' => ['editor-a', 'PATCH', 'editor', 'grace', 'authors'];
        yield 'reader cannot add tag' => ['reader', 'POST', 'tags', 'unused-tag', 'tags'];
        yield 'reader cannot remove tag' => ['reader', 'DELETE', 'tags', 'php', 'tags'];
    }

    public function testEditorMayAssignSelfAndAdminMayChangeAuthor(): void
    {
        $this->decodeJsonApi($this->asUser('editor-a', 'PATCH', $this->url().'/relationships/editor', ['data' => $this->identifier('ada', 'authors')]));
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getEditor()->getId());
        $this->decodeJsonApi($this->asUser('admin', 'PATCH', $this->url().'/relationships/author', ['data' => $this->identifier('grace', 'authors')]));
        self::assertSame($this->ids['grace'], (string) $this->storedArticle()->getAuthor()->getId());
    }

    public function testTargetExistenceIsStillChecked(): void
    {
        $this->assertJsonApiError($this->asUser('admin', 'PATCH', $this->url().'/relationships/editor', ['data' => ['type' => 'authors', 'id' => '999999']]), 404);
        self::assertSame($this->ids['grace'], (string) $this->storedArticle()->getEditor()->getId());
    }

    public function testEmbeddedRelationshipWriteCannotBypassPolicy(): void
    {
        $body = $this->patchPayload(['title' => 'Must roll back']);
        $body['data']['relationships']['author'] = ['data' => $this->identifier('grace', 'authors')];
        $this->assertJsonApiError($this->asUser('editor-a', 'PATCH', $this->url(), $body), 403);
        self::assertSame('Shared title', $this->storedArticle()->getTitle());
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getAuthor()->getId());
    }

    public function testAtomicRelationshipCannotBypassPolicy(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles') + ['relationship' => 'author'], 'data' => $this->identifier('grace', 'authors')]], ['Authorization' => 'Bearer editor-a']), 403);
        self::assertSame($this->ids['ada'], (string) $this->storedArticle()->getAuthor()->getId());
    }
    public function testTagTargetMustExistAsWellAsBeingPermitted(): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', $this->url().'/relationships/tags', ['data' => [['type' => 'tags', 'id' => '999999']]]), 404);
        self::assertCount(2, $this->storedArticle()->getTags());
    }

}
