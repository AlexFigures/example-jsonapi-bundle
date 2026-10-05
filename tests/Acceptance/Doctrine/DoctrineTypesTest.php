<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Doctrine;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class DoctrineTypesTest extends AcceptanceTestCase
{
    public function testNativeReadTypes(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('article-2')));
        $attrs = $doc['data']['attributes'];
        self::assertIsString($doc['data']['id']);
        self::assertIsInt($attrs['views']);
        self::assertIsBool($attrs['featured']);
        self::assertIsFloat($attrs['rating']);
        self::assertSame('published', $attrs['status']);
        self::assertSame(['sequence' => 2, 'labels' => ['acceptance','demo']], $attrs['metadata']);
        self::assertSame('2026-01-02T12:00:00+00:00', $attrs['published-at']);
        self::assertSame('Body 2', $attrs['content']);
        $draft = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertNull($draft['data']['attributes']['published-at']);
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('WRITE-MODEL-SERIALIZER-METADATA')]
    public function testWriteTypesRoundTrip(): void
    {
        $payload = $this->patchPayload(['views' => 123, 'featured' => true, 'rating' => 3.25, 'metadata' => ['nested' => ['value' => null]], 'status' => 'published', 'published-at' => '2026-02-01T00:00:00+00:00']);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url(), $payload));
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        foreach ($payload['data']['attributes'] as $name => $value) { self::assertSame($value, $doc['data']['attributes'][$name]); }
    }

    public function testGeneratedUuid(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/subscriptions', ['data' => ['type' => 'subscriptions', 'attributes' => ['email' => 'uuid@example.test']]]), 201);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f-]{27}$/', $doc['data']['id']);
    }
}
