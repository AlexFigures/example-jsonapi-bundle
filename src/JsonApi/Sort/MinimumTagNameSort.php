<?php

declare(strict_types=1);

namespace App\JsonApi\Sort;

use AlexFigures\Symfony\Filter\Handler\SortHandlerInterface;
use Doctrine\ORM\QueryBuilder;

/** Explicit application semantics for otherwise ambiguous to-many ordering. */
final class MinimumTagNameSort implements SortHandlerInterface
{
    public function supports(string $field): bool { return $field === 'tags.name'; }
    public function getPriority(): int { return 100; }
    public function handle(string $field, bool $descending, object $queryBuilder): void
    {
        if (!$queryBuilder instanceof QueryBuilder) { throw new \LogicException('Aggregate sort requires Doctrine ORM.'); }
        $root = $queryBuilder->getRootAliases()[0];
        $suffix = count($queryBuilder->getDQLPart('select'));
        $link = 'sort_link_'.$suffix; $tag = 'sort_tag_'.$suffix; $alias = 'minimum_tag_name_'.$suffix;
        $queryBuilder->leftJoin($root.'.articleTags', $link)->leftJoin($link.'.tag', $tag)
            ->addSelect('MIN('.$tag.'.name) AS HIDDEN '.$alias)
            ->addGroupBy($root.'.id')->addOrderBy($alias, $descending ? 'DESC' : 'ASC');
    }
}
