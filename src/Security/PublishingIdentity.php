<?php

declare(strict_types=1);

namespace App\Security;

final readonly class PublishingIdentity
{
    public function __construct(public string $role, public string $authorEmail)
    {
    }
}
