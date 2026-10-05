<?php

declare(strict_types=1);

namespace App\Security;

use AlexFigures\Symfony\Contract\Data\ChangeSet;
use AlexFigures\Symfony\Contract\Data\ResourceIdentifier;
use AlexFigures\Symfony\Query\Criteria;
use Doctrine\ORM\QueryBuilder;

/** Policies are application code; dispatching them on every transport path is bundle code. */
final class PublishingRules
{
    public function __construct(private PublishingContext $context, private ArticlePolicy $policy)
    {
    }

    public function onBeforeFindCollection(string $type, Criteria $criteria): void
    {
        if (!$this->context->enabled || !in_array($type, ['articles', 'article-summaries'], true)) {
            return;
        }
        $user = $this->context->identity();
        if ($user->role !== 'admin') {
            $criteria->customConditions[] = static function (QueryBuilder $query) use ($user): void {
                $alias = $query->getRootAliases()[0];
                $query->innerJoin($alias.'.author', 'publishing_owner')
                    ->andWhere('publishing_owner.email = :publishing_owner_email')
                    ->setParameter('publishing_owner_email', $user->authorEmail);
            };
        }
    }

    public function onBeforeFindOne(string $type, string $id, Criteria $criteria): void
    {
        if ($this->context->enabled && in_array($type, ['articles', 'article-summaries'], true)) {
            $this->policy->authorize($this->policy->article($id), 'read');
        }
    }

    public function onBeforeCreate(string $type, ChangeSet $changeSet): void
    {
        if (!$this->context->enabled) { return; }
        if ($type !== 'articles') { $this->policy->requireAdmin(); return; }
        $this->policy->requireRole();
        // Author is ownership, not merely linkage existence.
        $target = $changeSet->relationships['author']['data'] ?? null;
        if ($this->context->identity()->role !== 'admin' && is_array($target) && isset($target['id'])) {
            // The association policy for creation uses the intended owner identity.
            // Resolve only the domain resource; the bundle still validates types and existence.
            $this->policy->newOwner((string) $target['id']);
        }
    }

    public function onBeforeUpdate(string $type, string $id, ChangeSet $changeSet): void
    {
        if (!$this->context->enabled) { return; }
        if ($type !== 'articles') { $this->policy->requireAdmin(); return; }
        $article = $this->policy->article($id);
        $this->policy->authorize($article, 'update');
        foreach ($changeSet->relationships as $name => $relationship) {
            $data = $relationship['data'];
            $rows = $data === null ? [] : (isset($data['type']) ? [$data] : $data);
            $targets = array_map(static fn (array $row): ResourceIdentifier => new ResourceIdentifier($row['type'], $row['id']), $rows);
            $this->policy->association($article, $name, $targets);
        }
    }

    public function onBeforeDelete(string $type, string $id): void
    {
        if (!$this->context->enabled) { return; }
        if ($type !== 'articles') { $this->policy->requireAdmin(); return; }
        $this->policy->authorize($this->policy->article($id), 'delete');
    }

    private function association(string $type, string $id, string $relationship, array $targets): void
    {
        if (!$this->context->enabled) { return; }
        if ($type !== 'articles') { $this->policy->requireAdmin(); return; }
        $this->policy->association($this->policy->article($id), $relationship, $targets);
    }

    public function onBeforeRelReplaceToMany(string $type, string $id, string $relationship, array $targets): void
    { $this->association($type, $id, $relationship, $targets); }
    public function onBeforeRelReplaceToOne(string $type, string $id, string $relationship, ?ResourceIdentifier $target): void
    { $this->association($type, $id, $relationship, $target === null ? [] : [$target]); }
    public function onBeforeRelAddToMany(string $type, string $id, string $relationship, array $targets): void
    { $this->association($type, $id, $relationship, $targets); }
    public function onBeforeRelRemoveFromToMany(string $type, string $id, string $relationship, array $targets): void
    { $this->association($type, $id, $relationship, $targets); }
}
