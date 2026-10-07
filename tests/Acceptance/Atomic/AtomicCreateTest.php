<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicCreateTest extends AcceptanceTestCase
{
    public function testCreateUsingHrefAndGeneratedId(): void
    {
        $doc = $this->decodeJsonApi($this->atomic([['op' => 'add', 'href' => '/api/articles', 'data' => $this->articlePayload()['data']]]));
        self::assertCount(1, $doc['atomic:results']);
        $resource = $doc['atomic:results'][0]['data'];
        $this->assertResourceObject($resource, 'articles');
        self::assertNotSame('', $resource['id']);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles/'.$resource['id']));
        self::assertSame('New article', $after['data']['attributes']['title']);
    }

    public function testCanonicalAddWithoutRefOrHref(): void
    {
        // EXPECTED_BUNDLE_GAP ATOMIC-001: target inferred from data.type.
        $doc = $this->decodeJsonApi($this->atomic([['op' => 'add', 'data' => $this->articlePayload()['data']]]));
        $this->assertResourceObject($doc['atomic:results'][0]['data'], 'articles');
        self::assertNotSame('', $doc['atomic:results'][0]['data']['id']);
    }

    public function testTitleOnlyCanonicalAddReachesDomainValidation(): void
    {
        // EXPECTED_BUNDLE_GAP ATOMIC-001: this exact transport shape is valid.
        // Our domain also requires slug and author: report 422, never "missing target" 400.
        $this->assertJsonApiError($this->atomic([['op' => 'add', 'data' => ['type' => 'articles', 'attributes' => ['title' => 'Created atomically']]]]), 422);
    }
}
