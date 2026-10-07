<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Tagged;

use AlexFigures\Symfony\Resource\Attribute\{JsonApiResource, Id, Attribute};
use AlexFigures\Symfony\Resource\Definition\{ReadProjection, ResourceOperation};
use App\PgEntity\FeatureArticle;

#[JsonApiResource(type: 'tagged-articles', operations: [ResourceOperation::INDEX, ResourceOperation::SHOW], dataClass: FeatureArticle::class, viewClass: TaggedArticle::class, readProjection: ReadProjection::DTO, fieldMap: ['id' => 'e.id', 'title' => 'e.title'])]
final class TaggedArticle
{
    #[Id] public ?int $id = null;
    #[Attribute] public string $title = '';
}
