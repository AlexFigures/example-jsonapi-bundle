<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Docs;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledDocumentationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_docs_off'; }

    public function testDisabledSpecAndUiDoNotServeDocuments(): void
    {
        foreach (['/_jsonapi/openapi.json', '/_jsonapi/docs'] as $url) {
            self::assertSame(404, $this->requestJsonApi('GET', $url, headers: ['Accept' => '*/*'])->getStatusCode());
        }
    }
}
