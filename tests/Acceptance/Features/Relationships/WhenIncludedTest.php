<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class WhenIncludedTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_linkage'; }

    public function testLinkageAppearsOnlyWithRequestedInclude(): void
    {
        $plain = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertArrayNotHasKey('data', $plain['data']['relationships']['author']);
        $included = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author'));
        self::assertSame($this->ids['ada'], $included['data']['relationships']['author']['data']['id']);
    }
}
