<?php

declare(strict_types=1);

namespace App\JsonApi;

use AlexFigures\JsonApi\Contract\Data\{ResourceRepository, Slice};
use AlexFigures\JsonApi\Bridge\Doctrine\Query\DoctrineCollectionQueryProviderInterface;
use AlexFigures\JsonApi\Query\Criteria;
use App\Security\PublishingRules;
use Doctrine\ORM\QueryBuilder;

/** Add policy, delegate generic query behavior. Optional query-plan forwarding must
 * carry the same visibility predicate through the public optional bundle capability.
 */
final class ScopedArticleRepository implements ResourceRepository, DoctrineCollectionQueryProviderInterface
{
    public function __construct(private ResourceRepository $inner, private PublishingRules $rules)
    {
    }

    public function findCollection(string $type, Criteria $criteria): Slice
    {
        $criteria = clone $criteria;
        $this->rules->onBeforeFindCollection($type, $criteria);
        return $this->inner->findCollection($type, $criteria);
    }

    public function collectionQuery(string $type, Criteria $criteria): ?QueryBuilder
    {
        $criteria = clone $criteria;
        $this->rules->onBeforeFindCollection($type, $criteria);
        if (!$this->inner instanceof DoctrineCollectionQueryProviderInterface) {
            return null;
        }

        return $this->inner->collectionQuery($type, $criteria);
    }

    public function findOne(string $type, string $id, Criteria $criteria): ?object
    {
        $this->rules->onBeforeFindOne($type, $id, $criteria);
        return $this->inner->findOne($type, $id, $criteria);
    }

    public function findRelated(string $type, string $relationship, array $identifiers): iterable
    {
        foreach ($identifiers as $identifier) {
            $this->rules->onBeforeFindOne($type, $identifier->id, new Criteria());
        }
        return $this->inner->findRelated($type, $relationship, $identifiers);
    }
}
