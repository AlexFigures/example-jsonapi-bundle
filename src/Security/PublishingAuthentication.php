<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/** Authentication at the HTTP boundary; authorization remains resource-specific. */
final class PublishingAuthentication
{
    public function __construct(private PublishingContext $context) {}

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 8)]
    public function authenticate(RequestEvent $event): void
    {
        if ($event->isMainRequest() && $this->context->enabled && str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            $this->context->identity();
        }
    }
}
