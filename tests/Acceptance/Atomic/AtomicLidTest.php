<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicLidTest extends AcceptanceTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('ATOMIC-004')]
    public function testLocalIdsAcrossCreateAndRelationshipOperations(): void
    {
        $article = $this->articlePayload()['data'];
        $article['lid'] = 'article-a';
        $article['relationships']['author']['data'] = ['type' => 'authors', 'lid' => 'author-a'];
        $operations = [
            ['op' => 'add', 'href' => '/api/authors', 'data' => ['type' => 'authors', 'lid' => 'author-a', 'attributes' => ['name' => 'Local Author']]],
            ['op' => 'add', 'href' => '/api/articles', 'data' => $article],
            ['op' => 'update', 'ref' => ['type' => 'articles', 'lid' => 'article-a', 'relationship' => 'editor'], 'data' => ['type' => 'authors', 'lid' => 'author-a']],
        ];
        $doc = $this->decodeJsonApi($this->atomic($operations));
        self::assertCount(3, $doc['atomic:results']);
        $authorId = $doc['atomic:results'][0]['data']['id'];
        $articleId = $doc['atomic:results'][1]['data']['id'];
        self::assertNotSame('', $authorId);
        self::assertNotSame('', $articleId);
        self::assertSame([], $doc['atomic:results'][2]);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles/'.$articleId.'?include=author,editor'));
        self::assertSame($authorId, $after['data']['relationships']['author']['data']['id']);
        self::assertSame($authorId, $after['data']['relationships']['editor']['data']['id']);
        self::assertCount(1, $after['included']);
    }

    public function testUnknownLid(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => ['type' => 'authors', 'lid' => 'unknown'], 'data' => ['type' => 'authors', 'attributes' => ['name' => 'Unknown']]]]), 400);
    }

    public function testDuplicateLid(): void
    {
        $op = ['op' => 'add', 'href' => '/api/authors', 'data' => ['type' => 'authors', 'lid' => 'duplicate', 'attributes' => ['name' => 'Duplicate Local']]];
        $this->assertJsonApiError($this->atomic([$op, $op]), 400);
    }

    public function testLidTypeSafety(): void
    {
        $ops = [
            ['op' => 'add', 'href' => '/api/authors', 'data' => ['type' => 'authors', 'lid' => 'author-a', 'attributes' => ['name' => 'Local Author']]],
            ['op' => 'update', 'ref' => ['type' => 'tags', 'lid' => 'author-a'], 'data' => ['type' => 'tags', 'attributes' => ['name' => 'Wrong Type']]],
        ];
        $this->assertJsonApiError($this->atomic($ops), 400);
    }
    public function testNaturalIdLocalIdentifierResolvesAcrossOperations(): void
    {
        $response = $this->atomic([
            ['op' => 'add', 'href' => '/api/subscriptions', 'data' => ['type' => 'subscriptions', 'lid' => 'subscription-a', 'attributes' => ['email' => 'lid@example.test']]],
            ['op' => 'update', 'ref' => ['type' => 'subscriptions', 'lid' => 'subscription-a'], 'data' => ['type' => 'subscriptions', 'attributes' => ['email' => 'updated-lid@example.test']]],
        ]);
        $doc = $this->decodeJsonApi($response);
        self::assertCount(2, $doc['atomic:results']);
        $id = $doc['atomic:results'][0]['data']['id'];
        self::assertNotEmpty($id);
        self::assertSame($id, $doc['atomic:results'][1]['data']['id']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/subscriptions/'.$id));
        self::assertSame('updated-lid@example.test', $after['data']['attributes']['email']);
    }

}
