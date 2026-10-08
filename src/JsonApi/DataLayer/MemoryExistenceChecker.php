<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\JsonApi\Contract\Data\ExistenceChecker;

final class MemoryExistenceChecker implements ExistenceChecker
{
    public function exists(string $type, string $id): bool
    {
        return match ($type) {
            'memory-cards' => $id === 'card',
            'memory-notes' => $id === 'note',
            'memory-articles' => in_array($id, ['one', 'two'], true),
            default => false,
        };
    }
}
