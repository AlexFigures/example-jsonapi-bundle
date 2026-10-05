<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Events;

use AlexFigures\Symfony\Events\{ResourceChangedEvent, RelationshipChangedEvent};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/** HTTP-observable test instrumentation; no application response replacement. */
final class HttpEventRecorder
{
    public function __construct(private RequestStack $requests, private ManagerRegistry $doctrine) {}

    #[AsEventListener]
    public function resource(ResourceChangedEvent $event): void
    {
        $this->record('resource', $event->type, $event->operation);
    }

    #[AsEventListener]
    public function relationship(RelationshipChangedEvent $event): void
    {
        $this->record('relationship', $event->type, $event->operation);
    }

    private function record(string $kind, string $type, string $operation): void
    {
        $request = $this->requests->getCurrentRequest();
        if ($request === null) { return; }
        $events = $request->attributes->get('_cookbook_events', []);
        $events[] = ['kind' => $kind, 'type' => $type, 'operation' => $operation, 'transaction_active' => $this->doctrine->getConnection('pgsql')->isTransactionActive()];
        $request->attributes->set('_cookbook_events', $events);
    }

    #[AsEventListener(event: 'kernel.response', priority: -500)]
    public function response(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Cookbook-Events', json_encode($event->getRequest()->attributes->get('_cookbook_events', []), JSON_THROW_ON_ERROR));
    }
}
