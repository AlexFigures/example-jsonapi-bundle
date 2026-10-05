<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class WriteSurfaceTest extends ProductionTestCase
{
    #[DataProvider('protectedAttributes')]
    public function testProtectedFieldsOnCreate(string $attribute, mixed $value): void
    {
        $this->assertJsonApiError($this->asUser('admin', 'POST', '/api/articles', $this->articlePayload([$attribute => $value])), 422, '/data/attributes/'.$attribute);
    }

    #[DataProvider('protectedAttributes')]
    public function testProtectedFieldsOnUpdate(string $attribute, mixed $value): void
    {
        $before = $this->storedArticle();
        $this->assertJsonApiError($this->asUser('admin', 'PATCH', $this->url(), $this->patchPayload([$attribute => $value])), 422, '/data/attributes/'.$attribute);
        $after = $this->storedArticle();
        self::assertEquals($before->getCreatedAt(), $after->getCreatedAt());
        self::assertEquals($before->getUpdatedAt(), $after->getUpdatedAt());
        self::assertSame($before->getViews(), $after->getViews());
        self::assertSame($before->getStatus(), $after->getStatus());
        self::assertEquals($before->getPublishedAt(), $after->getPublishedAt());
    }

    public static function protectedAttributes(): iterable
    {
        yield 'creation time' => ['createdAt', '1990-01-01T00:00:00Z'];
        yield 'update time' => ['updatedAt', '1990-01-01T00:00:00Z'];
        yield 'counter' => ['views', 999];
        yield 'publication time' => ['published-at', '1990-01-01T00:00:00Z'];
        yield 'state' => ['status', 'published'];
    }

    public function testAtomicCannotWritePublicationState(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['status' => 'published'])['data']]], ['Authorization' => 'Bearer admin']), 422);
        self::assertSame('draft', $this->storedArticle()->getStatus()->value);
    }
}
