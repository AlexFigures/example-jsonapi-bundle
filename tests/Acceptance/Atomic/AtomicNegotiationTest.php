<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicNegotiationTest extends AcceptanceTestCase
{
    #[DataProvider('headers')]
    public function testAtomicMediaNegotiation(?string $contentType, ?string $accept, int $status): void
    {
        $response = $this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Negotiated atomic'])['data']]], ['Content-Type' => $contentType, 'Accept' => $accept]);
        if ($status === 200) {
            $this->decodeJsonApi($response);
            self::assertStringContainsString('ext="https://jsonapi.org/ext/atomic"', $response->headers->get('Content-Type'));
            self::assertContains('Accept', $response->getVary());
        } else { $this->assertJsonApiError($response, $status); }
    }

    public static function headers(): iterable
    {
        yield 'missing content type' => [null,self::ATOMIC,415];
        yield 'base content type' => [self::MEDIA,self::ATOMIC,415];
        yield 'correct' => [self::ATOMIC,self::ATOMIC,200];
        yield 'wrong ext' => [self::MEDIA.';ext="https://example.test/unknown"',self::ATOMIC,415];
        yield 'missing accept' => [self::ATOMIC,null,200];
        yield 'base accept' => [self::ATOMIC,self::MEDIA,406];
        yield 'multiple valid candidates' => [self::ATOMIC,self::MEDIA.', '.self::ATOMIC,200];
    }

    public function testAtomicAcceptWithQuality(): void
    {
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Quality atomic'])['data']]], ['Accept' => self::ATOMIC.';q=0.8']));
    }

    public function testAtomicMixedInvalidValidAccept(): void
    {
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Mixed atomic'])['data']]], ['Accept' => self::MEDIA.';foo=bar, '.self::ATOMIC]));
    }
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('ATOMIC-012')]
    public function testEnabledAtomicExtensionWithStrictGlobalNegotiation(): void
    {
        $op = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Strict atomic'])['data']];
        $this->decodeJsonApi($this->requestJsonApi('POST', '/api/strict-operations', ['atomic:operations' => [$op]], ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC]));
    }

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('ATOMIC-013')]
    public function testWrongBaseMediaTypeWithAtomicExtIsRejected(): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Wrong media'])['data']]], ['Content-Type' => 'text/plain;ext="https://jsonapi.org/ext/atomic"']), 415);
    }
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('ATOMIC-013')]
    public function testAtomicMediaParameterRejected(): void
    {
        $op = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Unsupported parameter'])['data']];
        $this->assertJsonApiError($this->atomic([$op], ['Content-Type' => self::ATOMIC.';charset=utf-8']), 415);
    }

    public function testUnknownAdditionalExtensionRejected(): void
    {
        $op = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Multiple extensions'])['data']];
        $this->assertJsonApiError($this->atomic([$op], ['Content-Type' => self::MEDIA.';ext="https://jsonapi.org/ext/atomic https://example.test/unknown"']), 415);
    }

}
