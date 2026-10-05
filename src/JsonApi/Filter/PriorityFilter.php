<?php

declare(strict_types=1);

namespace App\JsonApi\Filter;

use AlexFigures\Symfony\Filter\Handler\FilterHandlerInterface;
use Doctrine\ORM\QueryBuilder;

/** Two configured services demonstrate registry ordering without overriding core parsing. */
final class PriorityFilter implements FilterHandlerInterface
{
    public function __construct(private readonly int $priority = 0, private readonly string $title = 'Beta') {}
    public function supports(string $field, string $operator): bool { return $field === 'priority-search' && $operator === 'eq'; }
    public function handle(string $field, string $operator, array $values, object $queryBuilder): void
    {
        if (!$queryBuilder instanceof QueryBuilder) { throw new \LogicException('Doctrine query required.'); }
        $root = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere($root.'.title = :priority_title')->setParameter('priority_title', $this->title);
    }
    public function getPriority(): int { return $this->priority; }
}
