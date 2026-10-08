<?php

declare(strict_types=1);

namespace App\Api;

use AlexFigures\JsonApi\Resource\Attribute\Attribute;
use AlexFigures\JsonApi\Resource\Attribute\Id;
use AlexFigures\JsonApi\Resource\Attribute\JsonApiResource;
use AlexFigures\JsonApi\Resource\Definition\ReadProjection;
use AlexFigures\JsonApi\Resource\Definition\ResourceOperation;
use App\PgEntity\Article;

#[JsonApiResource(
    type: 'article-summaries',
    operations: [ResourceOperation::INDEX, ResourceOperation::SHOW],
    dataClass: Article::class,
    viewClass: ArticleSummary::class,
    readProjection: ReadProjection::DTO,
    fieldMap: ['id' => 'e.id', 'headline' => 'e.title'],
)]
final class ArticleSummary
{
    public function __construct(
        #[Id] public readonly int $id,
        #[Attribute] public readonly string $headline,
    ) {
    }
}
