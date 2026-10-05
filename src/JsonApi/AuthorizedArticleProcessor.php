<?php

declare(strict_types=1);

namespace App\JsonApi;

use AlexFigures\Symfony\Contract\Data\{ResourceProcessor, ChangeSet};
use App\Security\PublishingRules;

final class AuthorizedArticleProcessor implements ResourceProcessor
{
    public function __construct(private ResourceProcessor $inner, private PublishingRules $rules)
    {
    }

    public function processCreate(string $type, ChangeSet $changes, ?string $clientId = null): object
    {
        $this->rules->onBeforeCreate($type, $changes);
        return $this->inner->processCreate($type, $changes, $clientId);
    }

    public function processUpdate(string $type, string $id, ChangeSet $changes): object
    {
        $this->rules->onBeforeUpdate($type, $id, $changes);
        return $this->inner->processUpdate($type, $id, $changes);
    }

    public function processDelete(string $type, string $id): void
    {
        $this->rules->onBeforeDelete($type, $id);
        $this->inner->processDelete($type, $id);
    }
}
