<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledUiTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_ui_off'; }

    public function testUiCanBeDisabledIndependently(): void
    {
        self::assertSame(404, $this->requestJsonApi('GET', '/_jsonapi/docs', headers: ['Accept' => '*/*'])->getStatusCode());
        self::assertSame(200, $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*'])->getStatusCode());
    }
}
