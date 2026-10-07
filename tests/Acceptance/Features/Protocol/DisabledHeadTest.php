<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Protocol;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};

final class DisabledHeadTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_switches_off'; }
    public function testDisabledHeadIsUnavailableAndOptionsAgrees(): void
    {
        self::assertSame(405, $this->requestJsonApi('HEAD', $this->url())->getStatusCode());
        $options = $this->requestJsonApi('OPTIONS', $this->url());
        self::assertStringNotContainsString('HEAD', (string) $options->headers->get('Allow'));
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
    }
    public function testInlineRelationshipWritesCanBeDisabled(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url(), ['data' => ['type' => 'articles', 'id' => $this->ids['article-1'], 'relationships' => ['author' => ['data' => $this->identifier('grace', 'authors')]]]]), 400, '/data/relationships');
    }
}
