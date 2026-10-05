<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledGuardsTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features_unlimited';
    }

    public function testZeroDisablesStructuralGuards(): void
    {
        $filter = ['views' => ['in' => range(1, 250)]];
        for ($i = 0; $i < 12; ++$i) {
            $filter = ['and' => [$filter, ['views' => ['gt' => 0]]]];
        }
        $document = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles?'.http_build_query(['filter' => $filter])));
        self::assertNotEmpty($document['data']);
    }
}
