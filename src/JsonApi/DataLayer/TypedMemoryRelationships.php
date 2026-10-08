<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\JsonApi\Contract\Data\{TypedRelationshipReader, TypedRelationshipUpdater, ResourceIdentifier, Slice, SliceIds};
use AlexFigures\JsonApi\Query\{Criteria, Pagination};
use App\FeatureMemory\{MemoryCard, MemoryNote};

/** Endpoint adapter. Representation loading and durable transactions are separate capabilities. */
final class TypedMemoryRelationships implements TypedRelationshipReader, TypedRelationshipUpdater
{
    private ?string $related;
    private array $peers;

    public function __construct(private string $type, private string $sourceId)
    {
        $this->related = $sourceId;
        $this->peers = [$sourceId];
    }

    public function supports(string $type): bool { return $type === $this->type; }

    private function check(string $type, string $id): void
    {
        if (!$this->supports($type) || $id !== $this->sourceId) {
            throw new \LogicException('Relationship endpoint dispatched to the wrong adapter.');
        }
    }

    public function getToOneId(string $type, string $id, string $rel): ?string
    {
        $this->check($type, $id);
        return $this->related;
    }

    public function getToManyIds(string $type, string $id, string $rel, Pagination $pagination): SliceIds
    {
        $this->check($type, $id);
        return new SliceIds(array_slice($this->peers, ($pagination->number - 1) * $pagination->size, $pagination->size), $pagination->number, $pagination->size, count($this->peers));
    }

    private function resource(string $id): object
    {
        return $this->type === 'memory-cards' ? new MemoryCard($id, 'Card relationship adapter') : new MemoryNote($id, 'Note relationship adapter');
    }

    public function getRelatedResource(string $type, string $id, string $rel): ?object
    {
        $target = $this->getToOneId($type, $id, $rel);
        return $target === null ? null : $this->resource($target);
    }

    public function getRelatedCollection(string $type, string $id, string $rel, Criteria $criteria): Slice
    {
        $slice = $this->getToManyIds($type, $id, $rel, $criteria->pagination);
        return new Slice(array_map($this->resource(...), $slice->ids), $slice->pageNumber, $slice->pageSize, $slice->totalItems);
    }

    public function replaceToOne(string $type, string $id, string $rel, ?ResourceIdentifier $target): void
    {
        $this->check($type, $id);
        $this->related = $target?->id;
    }

    public function replaceToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->check($type, $id);
        $this->peers = array_values(array_unique(array_column($targets, 'id')));
    }

    public function addToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->check($type, $id);
        $this->peers = array_values(array_unique([...$this->peers, ...array_column($targets, 'id')]));
    }

    public function removeFromToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->check($type, $id);
        $this->peers = array_values(array_diff($this->peers, array_column($targets, 'id')));
    }
}
