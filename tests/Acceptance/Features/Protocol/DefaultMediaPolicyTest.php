<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Protocol;

use App\Tests\Acceptance\Support\{AcceptanceTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\DataProvider;

final class DefaultMediaPolicyTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_media_'.$this->dataName(); }
    #[ExpectedBundleGap('MEDIA-DEFAULT-POLICY')]
    #[DataProvider('policies')]
    public function testConfiguredDefaultMediaRequestAndResponsePolicy(string $mode): void
    {
        $response = $this->requestJsonApi('GET', $this->url(), headers: ['Content-Type' => 'application/json', 'Accept' => null]);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        self::assertSame($this->ids['article-1'], json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)['data']['id']);
        $body = ['data' => ['type' => 'authors', 'attributes' => ['name' => 'Media author', 'email' => 'media@example.test']]];
        $created = $this->requestJsonApi('POST', '/api/authors', $body, ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
        self::assertSame(201, $created->getStatusCode(), (string) $created->getContent());
        self::assertStringStartsWith('application/json', (string) $created->headers->get('Content-Type'));
        self::assertSame(415, $this->requestJsonApi('POST', '/api/authors', $body)->getStatusCode());
        self::assertSame(406, $this->requestJsonApi('GET', $this->url(), headers: ['Content-Type' => 'application/json', 'Accept' => 'text/plain'])->getStatusCode());
        if ($mode === 'default') {
            $negotiated = $this->requestJsonApi('GET', $this->url(), headers: ['Content-Type' => 'application/json']);
            self::assertSame(200, $negotiated->getStatusCode());
            self::assertStringStartsWith(self::MEDIA, (string) $negotiated->headers->get('Content-Type'));
        }
    }
    public static function policies(): iterable { yield 'default' => ['default']; yield 'legacy' => ['legacy']; }    #[ExpectedBundleGap('MEDIA-DEFAULT-POLICY')]
    #[DataProvider('policies')]
    public function testConfiguredRequestPolicyAppliesToGeneratedWrites(string $mode): void
    {
        $body = ['data' => ['type' => 'authors', 'attributes' => ['name' => 'Media author', 'email' => 'media@example.test']]];
        $response = $this->requestJsonApi('POST', '/api/authors', $body, ['Content-Type' => 'application/json', 'Accept' => 'application/json']);
        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());
        self::assertSame('Media author', json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)['data']['attributes']['name']);
        self::assertSame(415, $this->requestJsonApi('POST', '/api/authors', $body)->getStatusCode());
        self::assertSame(406, $this->requestJsonApi('GET', $this->url(), headers: ['Content-Type' => 'application/json', 'Accept' => 'text/plain'])->getStatusCode());
    }

}
