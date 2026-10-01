<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use AlexFigures\Symfony\Contract\Data\{ResourceRepository, Slice};
use AlexFigures\Symfony\Query\Criteria;
use Symfony\Component\HttpFoundation\RequestStack;

/** Public bundle adapter; registry routing also covers processors and relationship handlers. */
final class TenantRepository implements ResourceRepository
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
}
