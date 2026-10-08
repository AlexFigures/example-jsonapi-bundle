<?php

declare(strict_types=1);

namespace App\FeatureMemory;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute, Id, JsonApiResource, Relationship};

#[JsonApiResource(type: 'memory-notes')]
final readonly class MemoryNote
{
    public function __construct(#[Id] public string $id, #[Attribute] public string $title,
        #[Relationship(targetType: 'memory-notes')] public ?self $related = null,
        #[Relationship(targetType: 'memory-notes', toMany: true)] public array $peers = [],
    )
    {
    }
}
