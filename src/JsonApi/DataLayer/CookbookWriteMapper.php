<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\Symfony\Resource\Mapper\WriteMapperInterface;
use AlexFigures\Symfony\Resource\Definition\ResourceDefinition;
use AlexFigures\Symfony\Resource\Write\WriteContext;
use App\PgEntity\FeatureArticle;

/** Example application-owned input transformation; no JSON:API parsing or validation replacement. */
final readonly class CookbookWriteMapper implements WriteMapperInterface
{
    public function __construct(private WriteMapperInterface $inner, private string $prefix = '') {}

    public function instantiate(ResourceDefinition $definition, object $requestDto, WriteContext $context): object
    {
        return $this->inner->instantiate($definition, $requestDto, $context);
    }

    public function apply(object $entity, object $requestDto, ResourceDefinition $definition, WriteContext $context): void
    {
        $this->inner->apply($entity, $requestDto, $definition, $context);
        if ($entity instanceof FeatureArticle && isset($requestDto->title)) {
            $entity->title = $this->prefix.trim($requestDto->title);
        }
    }
}
