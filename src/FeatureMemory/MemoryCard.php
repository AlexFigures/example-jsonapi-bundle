<?php

declare(strict_types=1);

namespace App\FeatureMemory;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute, Id, JsonApiResource, Relationship};

#[JsonApiResource(type: 'memory-cards')]
final readonly class MemoryCard
{
    public function __construct(#[Id] public string $id, #[Attribute] public string $title,
        #[Relationship(targetType: 'memory-cards')] public ?self $related = null,
        #[Relationship(targetType: 'memory-cards', toMany: true)] public array $peers = [],
    )
    {
    }
}
