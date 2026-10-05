<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use AlexFigures\Symfony\Profile\ProfileContext;
use AlexFigures\Symfony\Resource\Definition\{VersionDefinition, VersionResolverInterface, ReadProjection};
use App\JsonApi\Profile\CookbookProfile;

/** Representation selection only; this does not implement optimistic locking. */
final class FeatureVersionResolver implements VersionResolverInterface
{
    public function resolve(ProfileContext $context): VersionDefinition
    {
        if ($context->has(CookbookProfile::URI)) {
            return new VersionDefinition(FeatureArticleView::class, [], ReadProjection::DTO, ['id' => 'e.id', 'title' => 'e.subtitle'], []);
        }
        return new VersionDefinition(null, [], ReadProjection::ENTITY, [], []);
    }
}
