<?php

declare(strict_types=1);

namespace App\JsonApi\Profile;

use AlexFigures\JsonApi\Profile\ProfileInterface;
use AlexFigures\JsonApi\Profile\ProfileContext;
use AlexFigures\JsonApi\Profile\Descriptor\ProfileDescriptor;
use AlexFigures\JsonApi\Profile\Validation\ProfileRequirements;
use AlexFigures\JsonApi\Profile\Hook\{DocumentHook, QueryHook, ReadHook, WriteHook, RelationshipHook, ResourceMetaHookInterface, FilterParameterProviderInterface, RelationshipFetchRequirementsHookInterface};
use AlexFigures\JsonApi\Contract\Data\{ChangeSet, ResourceIdentifier};
use AlexFigures\JsonApi\Query\Criteria;
use AlexFigures\JsonApi\Resource\Metadata\ResourceMetadata;
use AlexFigures\JsonApi\Http\Exception\ForbiddenException;
use Symfony\Component\HttpFoundation\Request;

/** Opt-in representation policy. Mandatory authorization belongs outside negotiated profiles. */
final class CookbookProfile implements ProfileInterface, DocumentHook, QueryHook, ReadHook, WriteHook, RelationshipHook, ResourceMetaHookInterface, FilterParameterProviderInterface, RelationshipFetchRequirementsHookInterface
{
    public const URI = 'urn:example:profile:cookbook';

    public function uri(): string { return self::URI; }
    public function descriptor(): ProfileDescriptor { return new ProfileDescriptor(self::URI, 'Cookbook hooks', '1.0'); }
    public function requirements(): ?ProfileRequirements { return null; }
    public function hooks(): iterable { return [$this, new CookbookCountPlan()]; }

    public function onParseQuery(ProfileContext $context, Request $request, Criteria $criteria): void
    {
        $criteria->pagination->size = min(isset($request->query->all()['filter']['cookbook_one']) ? 1 : 2, $criteria->pagination->size);
    }

    public function onBeforeFindCollection(ProfileContext $context, string $type, Criteria $criteria): void
    {
        if ($type === 'articles') {
            $criteria->customConditions[] = static function (object $qb): void {
                $root = $qb->getRootAliases()[0];
                $qb->andWhere("$root.views >= :cookbook_views")->setParameter('cookbook_views', 100);
            };
        }
    }

    public function onBeforeFindOne(ProfileContext $context, string $type, string $id, Criteria $criteria): void { if ($type === 'feature-memos') { throw new ForbiddenException('Cookbook profile hides memo items.'); } }
    public function onBeforeCreate(ProfileContext $context, string $type, ChangeSet $changeSet): void { $this->stamp($type, $changeSet); }
    public function onBeforeUpdate(ProfileContext $context, string $type, string $id, ChangeSet $changeSet): void { $this->stamp($type, $changeSet); }
    public function onBeforeDelete(ProfileContext $context, string $type, string $id): void { if ($type === 'feature-memos') { throw new ForbiddenException('Cookbook profile protects memo deletion.'); } }

    private function stamp(string $type, ChangeSet $changes): void
    {
        if (in_array($type, ['articles', 'feature-memos'], true) && isset($changes->attributes['title'])) {
            $changes->attributes['title'] = 'Profile: '.$changes->attributes['title'];
        }
    }

    public function onTopLevelLinks(ProfileContext $context, array &$links, Request $request): void { $links['describedby'] = 'https://example.test/cookbook'; }
    public function onResourceRelationships(ProfileContext $context, ResourceMetadata $metadata, array &$relationshipsPayload, object $model): void
    {
        foreach ($relationshipsPayload as $name => &$payload) {
            $payload['meta']['cookbook'] = true;
            $idPath = $metadata->idPropertyPath ?? 'id';
            $id = (string) \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor()->getValue($model, $idPath);
            $count = $context->relationshipReads?->count($metadata->type, $id, $name);
            if ($count !== null) {
                $payload['meta']['cookbook_count'] = $count;
            }
        }
    }
    public function filterParameters(): array { return ['cookbook_one']; }
    public function relationshipReads(ResourceMetadata $metadata): array { return $metadata->type === 'articles' ? array_fill_keys(array_keys($metadata->relationships), 'count') : []; }
    public function onResourceMeta(ProfileContext $context, ResourceMetadata $metadata, array &$meta, object $model): void { $meta['cookbook_resource'] = $metadata->type; }

    public function onTopLevelMeta(ProfileContext $context, array &$meta): void { $meta['cookbook_profile'] = true; }

    public function onBeforeRelReplaceToMany(ProfileContext $context, string $type, string $id, string $relationship, array $targets): void { $this->protect($relationship); }
    public function onBeforeRelReplaceToOne(ProfileContext $context, string $type, string $id, string $relationship, ?ResourceIdentifier $target): void { $this->protect($relationship); }
    public function onBeforeRelAddToMany(ProfileContext $context, string $type, string $id, string $relationship, array $targets): void { $this->protect($relationship); }
    public function onBeforeRelRemoveFromToMany(ProfileContext $context, string $type, string $id, string $relationship, array $targets): void { $this->protect($relationship); }
    private function protect(string $relationship): void
    {
        throw new ForbiddenException('Cookbook profile locks relationship changes.');
    }
}
