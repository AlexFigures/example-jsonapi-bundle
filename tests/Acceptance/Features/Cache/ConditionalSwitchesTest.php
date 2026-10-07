<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConditionalSwitchesTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_switches_off'; }
    #[DataProvider('safeHeaders')]
    public function testDisabledSafeConditionalHeaderReturnsFullRepresentation(string $header): void
    {
        $first = $this->requestJsonApi('GET', $this->url());
        $validator = $first->headers->get($header === 'If-None-Match' ? 'ETag' : 'Last-Modified');
        self::assertNotEmpty($validator);
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->url(), headers: [$header => $validator]));
    }
    public static function safeHeaders(): iterable { yield ['If-None-Match']; yield ['If-Modified-Since']; }
    #[DataProvider('writeHeaders')]
    public function testDisabledWriteConditionalHeaderAllowsWrite(string $header, string $value): void
    {
        $this->client->disableReboot();
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => 'Conditional evaluation disabled']), [$header => $value]));
        self::assertSame('Conditional evaluation disabled', $doc['data']['attributes']['title']);
        self::assertSame('Conditional evaluation disabled', $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()))['data']['attributes']['title']);
    }
    public static function writeHeaders(): iterable { yield ['If-Match', '"obsolete"']; yield ['If-Unmodified-Since', 'Thu, 01 Jan 1998 00:00:00 GMT']; }
}
