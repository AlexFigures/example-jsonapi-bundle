<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use Doctrine\Persistence\{ManagerRegistry, ObjectManager, ObjectRepository};
use Symfony\Component\HttpFoundation\RequestStack;

/** Request-scoped routing; never retain a selected manager in a property. */
final class TenantRegistry implements ManagerRegistry
{
    public function __construct(private readonly ManagerRegistry $inner, private readonly RequestStack $requests) {}
    public function getManagerForClass(string $class): ?ObjectManager
    {
        if (str_starts_with($class, 'App\\PgEntity\\') || str_starts_with($class, 'App\\Torture\\Entity\\')) {
            $request = $this->requests->getCurrentRequest();
            $manager = $request?->headers->get('X-Tenant-ID') === 'tenant-b' ? 'shard_b' : 'pgsql';
            if ($request?->headers->get('X-Torture-Topology') === 'replica') { $manager = 'replicated'; }
            if ($request?->headers->get('X-Torture-Topology') === 'replica-failure') { $manager = 'broken_replica'; }
            return $this->inner->getManager($manager);
        }
        return $this->inner->getManagerForClass($class);
    }
    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): ObjectRepository { return ($persistentManagerName === null ? $this->getManagerForClass($persistentObject) : $this->getManager($persistentManagerName))->getRepository($persistentObject); }
    public function getDefaultManagerName(): string { return $this->inner->getDefaultManagerName(); }
    public function getManager(?string $name = null): ObjectManager { return $this->inner->getManager($name); }
    public function getManagers(): array { return $this->inner->getManagers(); }
    public function resetManager(?string $name = null): ObjectManager { return $this->inner->resetManager($name); }
    public function reset(): void
    {
        if (method_exists($this->inner, 'reset')) { $this->inner->reset(); }
    }
    public function getManagerNames(): array { return $this->inner->getManagerNames(); }
    public function getDefaultConnectionName(): string { return $this->inner->getDefaultConnectionName(); }
    public function getConnection(?string $name = null): object { return $this->inner->getConnection($name); }
    public function getConnections(): array { return $this->inner->getConnections(); }
    public function getConnectionNames(): array { return $this->inner->getConnectionNames(); }
}
