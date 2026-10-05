<?php

declare(strict_types=1);

namespace App\Api;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource, SortableFields};
use AlexFigures\Symfony\Resource\Definition\{ReadProjection, ResourceOperation};
use App\PgEntity\FeatureArticle;

#[JsonApiResource(type: 'feature-custom-summaries', operations: [ResourceOperation::INDEX, ResourceOperation::SHOW], dataClass: FeatureArticle::class, readProjection: ReadProjection::CUSTOM)]
#[SortableFields(['id', 'title'])]
final readonly class FeatureCustomSummary
{
    public function __construct(#[Id] public int $resourceId, #[Attribute] public string $headline) {}
}
