<?php

declare(strict_types=1);

namespace App\FeatureMemory;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute, Id, JsonApiResource, Relationship};

#[JsonApiResource(type: 'memory-articles')]
final class MemoryArticle
{
    public function __construct(
        #[Id] public string $id = 'one',
        #[Attribute] public string $title = 'In-memory article',
        #[Relationship(targetType: 'memory-articles')] public ?self $related = null,
    ) {
    }
}
