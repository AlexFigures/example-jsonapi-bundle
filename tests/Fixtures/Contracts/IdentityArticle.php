<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Contracts;

use AlexFigures\JsonApi\Resource\Attribute\{JsonApiResource, Id, Attribute};
use AlexFigures\JsonApi\Resource\Definition\{ReadProjection, ResourceOperation};
use App\PgEntity\FeatureArticle;

#[JsonApiResource(type: 'identity-articles', exposeId: false, description: 'Identity remains required by JSON:API', operations: [ResourceOperation::INDEX, ResourceOperation::SHOW], dataClass: FeatureArticle::class, viewClass: IdentityArticle::class, readProjection: ReadProjection::DTO, fieldMap: ['id' => 'e.id', 'title' => 'e.title'])]
final class IdentityArticle
{
    #[Id] public ?int $id = null;
    #[Attribute] public string $title = '';
}
