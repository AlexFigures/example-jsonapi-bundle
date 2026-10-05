<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DisabledAtomicTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_atomic_disabled'; }
    public function testDisabledAtomicExtensionCannotExecuteAMutation(): void
    {
        $response = $this->atomic([['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']]]]);
        self::assertSame(404, $response->getStatusCode(), (string) $response->getContent());
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        $spec = $this->requestJsonApi('GET', '/_jsonapi/openapi.json', headers: ['Accept' => '*/*']);
        self::assertSame(200, $spec->getStatusCode());
        self::assertArrayNotHasKey('/api/operations', json_decode((string) $spec->getContent(), true)['paths']);
    }
}
