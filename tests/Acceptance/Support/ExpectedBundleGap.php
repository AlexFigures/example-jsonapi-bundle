<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class ExpectedBundleGap
{
    /** @param list<string> $datasets Empty means every dataset on this method. */
    public function __construct(public string $id, public array $datasets = [])
    {
    }
}
