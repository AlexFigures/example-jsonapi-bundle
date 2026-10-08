<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\JsonApi\Contract\Data\{TypedResourcePersister, ChangeSet};
use App\FeatureMemory\{MemoryCard, MemoryNote};

/** Uses the legacy public contract and documented jsonapi.persister tag intentionally. */
final class TypedMemoryPersister implements TypedResourcePersister
{
    public function __construct(private readonly string $type = 'disabled') {}
    public function supports(string $type): bool { return $type === $this->type; }
    public function create(string $type, ChangeSet $changes, ?string $clientId = null): object
    {
        return $this->model($clientId ?? 'typed', $changes);
    }
    public function update(string $type, string $id, ChangeSet $changes): object { return $this->model($id, $changes); }
    public function delete(string $type, string $id): void {}
    private function model(string $id, ChangeSet $changes): object
    {
        $title = 'Typed: '.($changes->attributes['title'] ?? '');
        return $this->type === 'memory-cards' ? new MemoryCard($id, $title) : new MemoryNote($id, $title);
    }
}
