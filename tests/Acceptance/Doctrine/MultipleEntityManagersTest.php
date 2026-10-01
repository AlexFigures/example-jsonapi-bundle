<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Doctrine;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class MultipleEntityManagersTest extends AcceptanceTestCase
{
    public function testInterleavedCrudUsesBothManagers(): void
    {
        $article = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/articles', $this->articlePayload()), 201)['data'];
        $payload = ['data' => ['type' => 'comments', 'attributes' => ['body' => 'Cross storage reference', 'articleId' => (int) $article['id'], 'authorName' => 'Reader']]];
        $comment = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/comments', $payload), 201)['data'];
        $articleUrl = '/api/articles/'.$article['id'];
        $commentUrl = '/api/comments/'.$comment['id'];
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $commentUrl, ['data' => ['type' => 'comments', 'id' => $comment['id'], 'attributes' => ['body' => 'Edited comment']]]));
        $read = $this->decodeJsonApi($this->requestJsonApi('GET', $commentUrl));
        self::assertSame('Edited comment', $read['data']['attributes']['body']);
        self::assertSame((int) $article['id'], $read['data']['attributes']['articleId']);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $articleUrl, ['data' => ['type' => 'articles', 'id' => $article['id'], 'attributes' => ['title' => 'Edited article']]]));
        self::assertSame(204, $this->requestJsonApi('DELETE', $commentUrl)->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', $commentUrl), 404);
        $this->decodeJsonApi($this->requestJsonApi('GET', $articleUrl));
        self::assertSame(204, $this->requestJsonApi('DELETE', $articleUrl)->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', $articleUrl), 404);
    }

    public function testSameIdentifierInDifferentStorageDoesNotCollide(): void
    {
        self::assertSame($this->ids['article-1'], $this->ids['comment']);
        $pg = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        $my = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('comment', 'comments')));
        $this->assertResourceIdentifier($pg['data'], 'articles', $this->ids['article-1']);
        $this->assertResourceIdentifier($my['data'], 'comments', $this->ids['comment']);
        self::assertSame('Useful article', $my['data']['attributes']['body']);
    }
}
