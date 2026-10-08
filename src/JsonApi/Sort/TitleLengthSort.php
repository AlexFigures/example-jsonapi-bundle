<?php

declare(strict_types=1);

namespace App\JsonApi\Sort;

use AlexFigures\JsonApi\Filter\Handler\SortHandlerInterface;
use Doctrine\ORM\QueryBuilder;

final class TitleLengthSort implements SortHandlerInterface
{
    public function supports(string $field): bool
    {
        return $field === 'title-length';
    }

    public function handle(string $field, bool $descending, object $queryBuilder): void
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \LogicException('Title length requires Doctrine ORM.');
        }
        $root = $queryBuilder->getRootAliases()[0];
        $alias = 'title_length_'.count($queryBuilder->getDQLPart('select'));
        $queryBuilder->addSelect("LENGTH($root.title) AS HIDDEN $alias")
            ->addOrderBy($alias, $descending ? 'DESC' : 'ASC');
    }

    public function getPriority(): int
    {
        return 10;
    }
}
