<?php

declare(strict_types=1);

namespace App\Security;

/** Application-owned identity adapter for the builtin audit profile's callable contract. */
final readonly class AuditIdentity
{
    public function __construct(private PublishingContext $context) {}
    public function __invoke(): string { return $this->context->identity()->authorEmail; }
}
