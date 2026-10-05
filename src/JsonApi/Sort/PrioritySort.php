<?php

declare(strict_types=1);

namespace App\JsonApi\Sort;

use AlexFigures\Symfony\Filter\Handler\SortHandlerInterface;
use Doctrine\ORM\QueryBuilder;

final class PrioritySort implements SortHandlerInterface
{
    public function __construct(private readonly int $priority = 0, private readonly bool $byLength = false) {}
    public function supports(string $field): bool { return $field === 'priority-sort'; }
    public function handle(string $field, bool $descending, object $queryBuilder): void
    {
        if (!$queryBuilder instanceof QueryBuilder) { throw new \LogicException('Doctrine query required.'); }
        $root = $queryBuilder->getRootAliases()[0];
        $expression = $this->byLength ? 'LENGTH('.$root.'.title)' : $root.'.title';
        $alias = 'priority_sort_'.count($queryBuilder->getDQLPart('select'));
        $queryBuilder->addSelect($expression.' AS HIDDEN '.$alias)->addOrderBy($alias, $descending ? 'DESC' : 'ASC');
    }
    public function getPriority(): int { return $this->priority; }
}
