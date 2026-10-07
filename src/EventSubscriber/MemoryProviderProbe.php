<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\JsonApi\DataLayer\MemoryArticleProvider;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/** Test-environment observability for the application-owned adapter. */
final readonly class MemoryProviderProbe
{
    public function __construct(private MemoryArticleProvider $provider) {}
    public function onResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Example-Write-Scopes', json_encode($this->provider->writeScopes, JSON_THROW_ON_ERROR));
        $event->getResponse()->headers->set('X-Example-Preloads', (string) $this->provider->preloadCalls);
    }
}
