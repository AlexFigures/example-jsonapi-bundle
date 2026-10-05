<?php

declare(strict_types=1);

namespace App\FeatureMemory;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource};

#[JsonApiResource(type: 'memory-notes')]
final readonly class MemoryNote
{
    public function __construct(#[Id] public string $id, #[Attribute] public string $title)
    {
    }
}
