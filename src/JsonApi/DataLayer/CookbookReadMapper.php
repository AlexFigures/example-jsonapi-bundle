<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\JsonApi\Resource\Mapper\ReadMapperInterface;
use AlexFigures\JsonApi\Resource\Definition\ResourceDefinition;
use AlexFigures\JsonApi\Query\Criteria;
use App\Api\FeatureCustomSummary;
use App\PgEntity\FeatureArticle;

final class CookbookReadMapper implements ReadMapperInterface
{
    public function __construct(private ReadMapperInterface $inner) {}

    public function toView(mixed $row, ResourceDefinition $definition, Criteria $criteria): object
    {
        if ($definition->type === 'feature-custom-summaries' && $row instanceof FeatureArticle) {
            return new FeatureCustomSummary($row->id, 'Custom: '.$row->title);
        }
        return $this->inner->toView($row, $definition, $criteria);
    }
}
