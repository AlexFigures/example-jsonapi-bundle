<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicUpdateTest extends AcceptanceTestCase
{
    #[DataProvider('targets')]
    public function testUpdateUsingExplicitTarget(string $kind): void
    {
        $target = $kind === 'ref' ? ['ref' => $this->identifier('article-1', 'articles')] : ['href' => $this->url()];
        $doc = $this->decodeJsonApi($this->atomic([['op' => 'update', 'data' => $this->patchPayload(['title' => 'Atomic update'])['data']] + $target]));
        self::assertCount(1, $doc['atomic:results']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Atomic update', $after['data']['attributes']['title']);
    }

    public static function targets(): iterable { yield ['ref']; yield ['href']; }

    #[ExpectedBundleGap('ATOMIC-002')]
    public function testUpdateTargetFromDataWithoutRefOrHref(): void
    {
        // EXPECTED_BUNDLE_GAP ATOMIC-002: use data.type and data.id.
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'data' => $this->patchPayload(['title' => 'Inferred target'])['data']]]));
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Inferred target', $after['data']['attributes']['title']);
    }
}
