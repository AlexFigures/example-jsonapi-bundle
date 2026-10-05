<?php

declare(strict_types=1);

namespace App\Api;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource, FilterableFields, SortableFields};
use AlexFigures\Symfony\Resource\Definition\{ReadProjection, ResourceOperation};
use App\PgEntity\FeatureArticle;

#[JsonApiResource(type: 'feature-summaries', operations: [ResourceOperation::INDEX, ResourceOperation::SHOW], dataClass: FeatureArticle::class, viewClass: FeatureSummary::class, readProjection: ReadProjection::DTO, fieldMap: ['resourceId' => 'e.id', 'headline' => 'e.title'])]
#[FilterableFields(['title'])]
#[SortableFields(['id', 'title'])]
final readonly class FeatureSummary
{
    public function __construct(#[Id] public int $resourceId, #[Attribute] public string $headline) {}
}
