<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicRemoveTest extends AcceptanceTestCase
{
    #[DataProvider('targets')]
    public function testRemove(string $kind): void
    {
        $target = $kind === 'ref' ? ['ref' => $this->identifier('article-1', 'articles')] : ['href' => $this->url()];
        $response = $this->atomic([['op' => 'remove'] + $target]);
        // return_policy=always selects the extension's 200 result-document response.
        $doc = $this->decodeJsonApi($response);
        self::assertCount(1, $doc['atomic:results']);
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url()), 404);
    }

    public static function targets(): iterable { yield ['ref']; yield ['href']; }

    public function testUnknownResource(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => '999999']]]), 404);
    }
}
