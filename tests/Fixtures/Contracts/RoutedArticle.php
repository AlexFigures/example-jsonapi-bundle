<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Contracts;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource};
use AlexFigures\Symfony\Resource\Definition\{ReadProjection, ResourceOperation};
use App\PgEntity\FeatureArticle;

#[JsonApiResource(type: 'routed-articles', routePrefix: '/reference', description: 'Per-resource routing contract', operations: [ResourceOperation::INDEX, ResourceOperation::SHOW], dataClass: FeatureArticle::class, viewClass: RoutedArticle::class, readProjection: ReadProjection::DTO, fieldMap: ['id' => 'e.id', 'title' => 'e.title'])]
final readonly class RoutedArticle
{
    public function __construct(#[Id] public int $id, #[Attribute] public string $title) {}
}
