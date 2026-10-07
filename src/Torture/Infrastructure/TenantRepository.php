<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use AlexFigures\Symfony\Contract\Data\{ResourceRepository, Slice};
use AlexFigures\Symfony\Bridge\Doctrine\Query\DoctrineCollectionQueryProviderInterface;
use AlexFigures\Symfony\Query\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/** Tenant policy adapter. Forward the public optional query-plan capability only
 * after tenant validation and authoritative provider selection.
 */
final class TenantRepository implements ResourceRepository, DoctrineCollectionQueryProviderInterface
{
    public function __construct(private readonly ResourceRepository $inner, private readonly RequestStack $requests) {}
    private function check(): void
    {
        $tenant = $this->requests->getCurrentRequest()?->headers->get('X-Tenant-ID', 'tenant-a');
        if (!in_array($tenant, ['tenant-a', 'tenant-b', null], true)) { TortureRequestSubscriber::reject(400, 'unknown-tenant', 'Unknown tenant.'); }
    }
    public function findCollection(string $type, Criteria $criteria): Slice { $this->check(); return $this->inner->findCollection($type, $criteria); }
    public function findOne(string $type, string $id, Criteria $criteria): ?object { $this->check(); return $this->inner->findOne($type, $id, $criteria); }
    public function findRelated(string $type, string $relationship, array $identifiers): iterable { $this->check(); return $this->inner->findRelated($type, $relationship, $identifiers); }

    public function collectionQuery(string $type, Criteria $criteria): ?QueryBuilder
    {
        // TenantRegistry routes the inner Doctrine repository to the current tenant's
        // manager. Keep the validation check on this optimized path as on normal reads.
        $this->check();
        $provider = $this->inner;
        if ($provider instanceof \AlexFigures\Symfony\Bridge\Symfony\Locator\ResourceRepositoryLocator) {
            // The bundle's locator selects a type-specific repository before asking
            // it for optional query capabilities, so preserve that dispatch here.
            $provider = $provider->getRepositoryForType($type);
        }
        if (!$provider instanceof DoctrineCollectionQueryProviderInterface) {
            return null;
        }

        return $provider->collectionQuery($type, $criteria);
    }
}
