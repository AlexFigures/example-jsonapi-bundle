<?php

declare(strict_types=1);

namespace App\JsonApi\Filter;

use AlexFigures\Symfony\Filter\Operator\AbstractOperator;
use AlexFigures\Symfony\Filter\Operator\DoctrineExpression;
use AlexFigures\Symfony\Http\Exception\BadRequestException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class StartsWithOperator extends AbstractOperator
{
    public function name(): string
    {
        return 'starts_with';
    }

    public function normalizeValues(mixed $raw): array
    {
        if (!is_string($raw) || $raw === '') {
            throw new BadRequestException('starts_with requires one nonempty string.');
        }
        return [$raw];
    }

    public function compile(string $rootAlias, string $dqlField, array $values, AbstractPlatform $platform): DoctrineExpression
    {
        $parameter = 'prefix_'.bin2hex(random_bytes(6));
        $value = strtr($values[0], ['!' => '!!', '%' => '!%', '_' => '!_']);
        return new DoctrineExpression("$dqlField LIKE :$parameter ESCAPE '!'", [$parameter => $value.'%']);
    }
}
