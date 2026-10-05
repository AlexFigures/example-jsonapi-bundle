<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AuthorizationTest extends ProductionTestCase
{
    #[DataProvider('credentials')]
    public function testAuthenticationOnGeneratedIndexAndShow(?string $credential, int $status): void
    {
        $headers = $credential === null ? [] : ['Authorization' => 'Bearer '.$credential];
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles', headers: $headers), $status);
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url(), headers: $headers), $status);
    }

    public static function credentials(): iterable { yield 'anonymous' => [null, 401]; yield 'invalid' => ['unknown', 401]; }

    #[DataProvider('roles')]
    public function testReadResource(string $user): void
    {
        $doc = $this->decodeJsonApi($this->asUser($user, 'GET', $this->url()));
        self::assertSame($this->ids['article-1'], $doc['data']['id']);
    }
    public static function roles(): iterable { yield ['reader']; yield ['editor-a']; yield ['admin']; }

    #[DataProvider('readerMutations')]
    public function testReaderCannotMutateGeneratedRoutesOrCommand(string $method, string $path): void
    {
        $body = $method === 'PATCH' ? $this->patchPayload(['title' => 'Forbidden edit']) : null;
        if ($path === 'create') { $body = $this->articlePayload(); }
        $url = $path === 'create' ? '/api/articles' : $this->url().($path === 'publish' ? '/publish' : '');
        $this->assertJsonApiError($this->asUser('reader', $method, $url, $body), 403);
        self::assertSame('Shared title', $this->storedArticle()->getTitle());
        self::assertSame(0, $this->notificationCount());
    }
    public static function readerMutations(): iterable
    {
        yield 'post' => ['POST', 'create']; yield 'patch' => ['PATCH', 'resource'];
        yield 'delete' => ['DELETE', 'resource']; yield 'publish' => ['POST', 'publish'];
    }

    #[DataProvider('foreignOperations')]
    public function testEditorCannotAccessAnotherOwner(string $method, string $path): void
    {
        $payload = $method === 'PATCH' ? $this->patchPayload(['title' => 'Forbidden'], 'article-9') : null;
        $this->assertJsonApiError($this->asUser('editor-a', $method, $this->url('article-9').$path, $payload), 403);
        self::assertSame('Article 09', $this->storedArticle('article-9')->getTitle());
        self::assertSame(0, $this->notificationCount());
    }
    public static function foreignOperations(): iterable
    {
        yield ['GET', '']; yield ['PATCH', '']; yield ['DELETE', '']; yield ['POST', '/publish'];
    }

    public function testEditorMayEditOwnAndAdminMayManageForeignArticle(): void
    {
        $this->decodeJsonApi($this->asUser('editor-a', 'PATCH', $this->url(), $this->patchPayload(['title' => 'Allowed edit'])));
        self::assertSame('Allowed edit', $this->storedArticle()->getTitle());
        $this->decodeJsonApi($this->asUser('admin', 'PATCH', $this->url('article-9'), $this->patchPayload(['title' => 'Admin edit'], 'article-9')));
        self::assertSame('Admin edit', $this->storedArticle('article-9')->getTitle());
        self::assertSame(204, $this->asUser('admin', 'DELETE', $this->url('article-9'))->getStatusCode());
        $this->assertJsonApiError($this->asUser('admin', 'GET', $this->url('article-9')), 404);
    }

    public function testEditorCannotCreateForAnotherOwner(): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', '/api/articles', $this->articlePayload(relationships: ['author' => ['data' => $this->identifier('grace', 'authors')]])), 403);
    }

    public function testAtomicUpdateCannotBypassAuthorization(): void
    {
        $response = $this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Atomic bypass'])['data']]], ['Authorization' => 'Bearer reader']);
        $this->assertJsonApiError($response, 403);
        self::assertSame('Shared title', $this->storedArticle()->getTitle());
    }
    public function testAuthenticationCoversCustomRelationshipAndAtomicRoutes(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/authors'), 401);
        $this->assertJsonApiError($this->requestJsonApi('POST', $this->url().'/publish'), 401);
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url().'/relationships/author'), 401);
        $this->assertJsonApiError($this->atomic([['op' => 'remove', 'ref' => $this->identifier('article-1', 'articles')]]), 401);
        self::assertSame('draft', $this->storedArticle()->getStatus()->value);
    }

    public function testEditorCannotTakeOwnershipThroughAuthorIdentityOrInverseLinkage(): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'PATCH', $this->url('grace', 'authors'), ['data' => ['type' => 'authors', 'id' => $this->ids['grace'], 'attributes' => ['email' => 'ada@example.test']]]), 403);
        $this->assertJsonApiError($this->asUser('editor-a', 'POST', $this->url('ada', 'authors').'/relationships/articles', ['data' => [$this->identifier('article-9', 'articles')]]), 403);
        self::assertSame($this->ids['grace'], (string) $this->storedArticle('article-9')->getAuthor()->getId());
    }

    public function testAtomicDeniedLaterOperationRollsBackEarlierAllowedEdit(): void
    {
        $operations = [];
        foreach (['article-1', 'article-9'] as $key) {
            $operations[] = ['op' => 'update', 'ref' => $this->identifier($key, 'articles'), 'data' => $this->patchPayload(['title' => 'Must not persist'], $key)['data']];
        }
        $this->assertJsonApiError($this->atomic($operations, ['Authorization' => 'Bearer editor-a']), 403);
        self::assertSame('Shared title', $this->storedArticle()->getTitle());
        self::assertSame('Article 09', $this->storedArticle('article-9')->getTitle());
    }

}
