<?php

declare(strict_types=1);

namespace App\Application\Article;

final readonly class ArticlePublished
{
    public function __construct(public int $articleId, public \DateTimeImmutable $publishedAt)
    {
    }
}
