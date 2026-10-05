<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\Symfony\Contract\Data\{TypedResourceRepository, Slice};
use AlexFigures\Symfony\Query\Criteria;
use App\FeatureMemory\{MemoryCard, MemoryNote};

final class TypedMemoryRepository implements TypedResourceRepository
{
    public function __construct(private string $type)
    {
    }

    public function supports(string $type): bool { return $type === $this->type; }
    private function resource(): object
    {
        return $this->type === 'memory-cards' ? new MemoryCard('card', 'Card provider') : new MemoryNote('note', 'Note provider');
    }
    public function findCollection(string $type, Criteria $criteria): Slice
    {
        $page = $criteria->pagination;
        return new Slice($page->number === 1 ? [$this->resource()] : [], $page->number, $page->size, 1);
    }
    public function findOne(string $type, string $id, Criteria $criteria): ?object
    {
        $resource = $this->resource();
        return $resource->id === $id ? $resource : null;
    }
    public function findRelated(string $type, string $relationship, array $identifiers): iterable { return []; }
}
