<?php

declare(strict_types=1);

namespace App\JsonApi\Filter;

use AlexFigures\Symfony\Filter\Handler\FilterHandlerInterface;
use AlexFigures\Symfony\Http\Exception\BadRequestException;
use Doctrine\ORM\QueryBuilder;

final class ArticleSearchFilter implements FilterHandlerInterface
{
    public function supports(string $field, string $operator): bool
    {
        return $field === 'search' && $operator === 'eq';
    }

    public function handle(string $field, string $operator, array $values, object $queryBuilder): void
    {
        if (count($values) !== 1 || !is_string($values[0]) || strlen(trim($values[0])) < 3 || strlen($values[0]) > 100) {
            throw new BadRequestException('Search requires one term of 3 to 100 bytes.');
        }
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new \LogicException('This search adapter requires Doctrine ORM.');
        }
        $alias = $queryBuilder->getRootAliases()[0];
        // Escape LIKE wildcards as well as binding the value. SQL is never interpolated.
        $term = strtr(strtolower(trim($values[0])), ['!' => '!!', '%' => '!%', '_' => '!_']);
        $parameter = 'article_search_'.count($queryBuilder->getParameters());
        $queryBuilder->andWhere("(LOWER($alias.title) LIKE :$parameter ESCAPE '!' OR LOWER($alias.content) LIKE :$parameter ESCAPE '!')")
            ->setParameter($parameter, '%'.$term.'%');
    }

    public function getPriority(): int
    {
        return 10;
    }
}
