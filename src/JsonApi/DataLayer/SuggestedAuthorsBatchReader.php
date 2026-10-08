<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\JsonApi\Contract\Data\{RelationshipBatchReaderInterface, ResourceRepository, ResourceIdentifier};
use AlexFigures\JsonApi\Http\Exception\BadRequestException;
use AlexFigures\JsonApi\Query\{Criteria, Pagination};
use AlexFigures\JsonApi\Query\Fetch\{RelationshipReadRequirements, RelationshipReadMap};
use App\PgEntity\FeatureArticle;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/** One owner frontier query; target reads retain the application's configured repository scope. */
final readonly class SuggestedAuthorsBatchReader implements RelationshipBatchReaderInterface
{
    public function __construct(private ManagerRegistry $doctrine, private ResourceRepository $repository) {}
    public function supports(string $type, string $relationship): bool { return $type === 'feature-articles' && $relationship === 'suggestedAuthors'; }
    public function read(RelationshipReadRequirements $requirements, Criteria $criteria, Request $request): RelationshipReadMap
    {
        $map = new RelationshipReadMap();
        if ($requirements->ownerIds === []) { return $map; }
        $em = $this->doctrine->getManagerForClass(FeatureArticle::class);
        $rows = $em->createQueryBuilder()->select('a.id AS ownerId', 'IDENTITY(a.author) AS authorId')
            ->from(FeatureArticle::class, 'a')->where('a.id IN (:owners)')->setParameter('owners', $requirements->ownerIds)->getQuery()->getArrayResult();
        $ids = array_values(array_unique(array_map('strval', array_filter(array_column($rows, 'authorId'), static fn ($id): bool => $id !== null))));
        $budgets = array_filter([$requirements->linkage ? $requirements->remainingIdentifiers : null, $requirements->include ? $requirements->remainingIncluded : null], static fn ($budget): bool => $budget !== null);
        $visible = [];
        if ($ids !== []) {
            $target = clone $criteria;
            $probeSize = $budgets === [] ? count($ids) : min(count($ids), min($budgets) + count($requirements->knownTargetIds) + 1);
            $target->pagination = new Pagination(1, max(1, $probeSize));
            $target->identifiersOnly = true;
            $target->customConditions[] = static function (object $qb) use ($ids): void {
                $root = $qb->getRootAliases()[0];
                $qb->andWhere($root.'.id IN (:suggested_author_ids)')->setParameter('suggested_author_ids', $ids);
            };
            foreach ($this->repository->findCollection('authors', $target)->items as $identifier) {
                $id = $identifier instanceof ResourceIdentifier ? $identifier->id : (string) $identifier->getId();
                $visible[$id] = true;
            }
            $newIds = array_diff(array_keys($visible), $requirements->knownTargetIds);
            foreach ($budgets as $budget) {
                if (count($newIds) > $budget) { throw new BadRequestException('Suggested author relationship exceeds document budget.'); }
            }
            if ($requirements->include && $visible !== []) {
                $target->identifiersOnly = false;
                $target->pagination = new Pagination(1, count($visible));
                foreach ($this->repository->findCollection('authors', $target)->items as $author) {
                    $map->remember('authors', (string) $author->getId(), $author);
                }
            }
        }
        foreach ($rows as $row) {
            $authorId = (string) $row['authorId'];
            $linkage = isset($visible[$authorId]) ? [['type' => 'authors', 'id' => $authorId]] : [];
            $map->put('feature-articles', (string) $row['ownerId'], 'suggestedAuthors', $linkage);
            if ($requirements->count) { $map->putCount('feature-articles', (string) $row['ownerId'], 'suggestedAuthors', count($linkage)); }
        }
        return $map;
    }
}
