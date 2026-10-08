<?php

declare(strict_types=1);

namespace App\JsonApi;

use AlexFigures\JsonApi\Contract\Data\{RelationshipUpdater, ResourceIdentifier};
use App\Security\PublishingRules;

final class AuthorizedArticleRelationships implements RelationshipUpdater
{
    public function __construct(private RelationshipUpdater $inner, private PublishingRules $rules)
    {
    }

    public function replaceToOne(string $type, string $id, string $rel, ?ResourceIdentifier $target): void
    {
        $this->rules->onBeforeRelReplaceToOne($type, $id, $rel, $target);
        $this->inner->replaceToOne($type, $id, $rel, $target);
    }
    public function replaceToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->rules->onBeforeRelReplaceToMany($type, $id, $rel, $targets);
        $this->inner->replaceToMany($type, $id, $rel, $targets);
    }
    public function addToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->rules->onBeforeRelAddToMany($type, $id, $rel, $targets);
        $this->inner->addToMany($type, $id, $rel, $targets);
    }
    public function removeFromToMany(string $type, string $id, string $rel, array $targets): void
    {
        $this->rules->onBeforeRelRemoveFromToMany($type, $id, $rel, $targets);
        $this->inner->removeFromToMany($type, $id, $rel, $targets);
    }
}
