<?php

declare(strict_types=1);

namespace App\JsonApi\Profile;

use AlexFigures\Symfony\Profile\Hook\{FetchPlanHookInterface, DocumentHook};
use AlexFigures\Symfony\Profile\ProfileContext;
use Symfony\Component\HttpFoundation\Request;
use AlexFigures\Symfony\Resource\Metadata\ResourceMetadata;

/** Separate hook demonstrates the legacy count-planning contract independently. */
final class CookbookCountPlan implements FetchPlanHookInterface, DocumentHook
{
    public function relationshipCounts(ResourceMetadata $metadata): array
    {
        return $metadata->type === 'authors' ? array_keys($metadata->relationships) : [];
    }
    public function onTopLevelLinks(ProfileContext $context, array &$links, Request $request): void {}
    public function onResourceRelationships(ProfileContext $context, ResourceMetadata $metadata, array &$relationshipsPayload, object $model): void {}
    public function onTopLevelMeta(ProfileContext $context, array &$meta): void {}

}
