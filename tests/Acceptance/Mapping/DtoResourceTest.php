<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class DtoResourceTest extends AcceptanceTestCase
{
    public function testReadProjectionDoesNotExposeEntityInternals(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/article-summaries/'.$this->ids['article-1']));
        $this->assertResourceIdentifier($doc['data'], 'article-summaries', $this->ids['article-1']);
        self::assertSame(['headline' => 'Shared title'], $doc['data']['attributes']);
        self::assertEmpty($doc['data']['relationships'] ?? []);
        self::assertArrayNotHasKey('content', $doc['data']['attributes']);
    }

    public function testDtoCollection(): void
    {
        $doc = $this->collection(type: 'article-summaries');
        self::assertCount(5, $doc['data']);
        foreach ($doc['data'] as $r) { self::assertSame(['headline'], array_keys($r['attributes'])); }
    }
}
