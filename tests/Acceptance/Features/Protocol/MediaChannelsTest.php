<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class MediaChannelsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_channels'; }

    #[DataProvider('channels')]
    public function testPublicChannelScopeAndResponse(string $channel): void
    {
        $response = $this->requestJsonApi('POST', '/cookbook/channel/'.$channel, [], ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
        self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        self::assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        self::assertSame($channel, json_decode((string) $response->getContent(), true)['channel']);
        self::assertSame(415, $this->requestJsonApi('POST', '/cookbook/channel/'.$channel, [])->getStatusCode());
    }

    public static function channels(): iterable
    {
        foreach (['path', 'route', 'attribute'] as $channel) { yield $channel => [$channel]; }
    }

    public function testDefaultChannelStillAppliesOutsideMatchedScope(): void
    {
        self::assertSame(406, $this->requestJsonApi('GET', $this->url(), headers: ['Accept' => 'application/json'])->getStatusCode());
    }    #[DataProvider('channels')]
    public function testChannelDefaultResponseAndNegotiableWhitelist(string $channel): void
    {
        $response = $this->requestJsonApi('POST', '/cookbook/channel/'.$channel, [], ['Content-Type' => 'application/json', 'Accept' => null]);
        self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        self::assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        self::assertSame(406, $this->requestJsonApi('POST', '/cookbook/channel/'.$channel, [], ['Content-Type' => 'application/json', 'Accept' => 'text/plain'])->getStatusCode());
    }

}
