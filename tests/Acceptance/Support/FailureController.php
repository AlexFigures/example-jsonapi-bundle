<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

final class FailureController
{
    public function __invoke(): never
    {
        throw new \RuntimeException('acceptance-private-secret');
    }
}
