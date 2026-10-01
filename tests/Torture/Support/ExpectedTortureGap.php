<?php

declare(strict_types=1);

namespace App\Tests\Torture\Support;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class ExpectedTortureGap
{
    public function __construct(public string $id) {}
}
