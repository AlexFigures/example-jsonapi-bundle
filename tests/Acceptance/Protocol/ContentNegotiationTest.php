<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ContentNegotiationTest extends AcceptanceTestCase
{
    #[DataProvider('acceptHeaders')]
    public function testAccept(?string $accept, int $status): void
    {
        $response = $this->requestJsonApi('GET', '/api/articles', headers: ['Accept' => $accept]);
        if ($status === 200) {
            $this->decodeJsonApi($response);
        } else {
            $this->assertJsonApiError($response, $status);
        }
        self::assertContains('Accept', $response->getVary());
    }

    public static function acceptHeaders(): iterable
    {
        yield 'absent' => [null, 200];
        yield 'wildcard' => ['*/*', 200];
        yield 'application wildcard' => ['application/*', 200];
        yield 'base' => [self::MEDIA, 200];
        yield 'q zero' => [self::MEDIA.';q=0', 406];
        yield 'other media' => ['text/html', 406];
        yield 'invalid variant only' => [self::MEDIA.';foo=bar', 406];
        yield 'unknown extension' => [self::MEDIA.';ext="https://example.test/unknown"', 406];
        yield 'unknown profile ignored' => [self::MEDIA.';profile="https://example.test/unknown"', 200];
        yield 'known profile' => [self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"', 200];
        yield 'multiple profiles' => [self::MEDIA.';profile="urn:jsonapi:profile:rel-counts https://example.test/unknown"', 200];
    }

    #[DataProvider('mixedCandidates')]
    #[ExpectedBundleGap('CONTENT-NEGOTIATION-001')]
    public function testMixedValidInvalidCandidates(string $accept): void
    {
        // EXPECTED_BUNDLE_GAP CONTENT-NEGOTIATION-001: ignore invalid candidates.
        $this->decodeJsonApi($this->requestJsonApi('GET', '/api/articles', headers: ['Accept' => $accept]));
    }

    public static function mixedCandidates(): iterable
    {
        yield 'invalid first' => [self::MEDIA.';foo=bar, '.self::MEDIA];
        yield 'valid first' => [self::MEDIA.', '.self::MEDIA.';foo=bar'];
        yield 'unknown ext then valid' => [self::MEDIA.';ext="https://example.test/unknown", '.self::MEDIA];
    }

    #[DataProvider('qualityValues')]
    #[ExpectedBundleGap('CONTENT-NEGOTIATION-002')]
    public function testQualityValues(string $accept, int $status): void
    {
        // EXPECTED_BUNDLE_GAP CONTENT-NEGOTIATION-002: q is HTTP negotiation metadata.
        $response = $this->requestJsonApi('GET', '/api/articles', headers: ['Accept' => $accept]);
        if ($status === 200) { $this->decodeJsonApi($response); }
        else { $this->assertJsonApiError($response, $status); }
    }

    public static function qualityValues(): iterable
    {
        yield 'q one' => [self::MEDIA.';q=1', 200];
        yield 'q fraction' => [self::MEDIA.';q=0.8', 200];
        yield 'fallback candidate' => ['text/html;q=1, '.self::MEDIA.';q=0.8', 200];
    }

    #[DataProvider('contentTypes')]
    public function testContentType(string $contentType, int $status): void
    {
        $response = $this->requestJsonApi('POST', '/api/authors', ['data' => ['type' => 'authors', 'attributes' => ['name' => 'Media Author']]], ['Content-Type' => $contentType]);
        if ($status === 201) { $this->decodeJsonApi($response, 201); }
        else { $this->assertJsonApiError($response, $status); }
    }

    public static function contentTypes(): iterable
    {
        yield 'base' => [self::MEDIA, 201];
        yield 'JSON' => ['application/json', 415];
        yield 'charset' => [self::MEDIA.';charset=utf-8', 415];
        yield 'unsupported parameter' => [self::MEDIA.';foo=bar', 415];
        yield 'unknown extension' => [self::MEDIA.';ext="https://example.test/unknown"', 415];
        yield 'unknown profile' => [self::MEDIA.';profile="https://example.test/unknown"', 201];
        yield 'known profile' => [self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"', 201];
        yield 'multiple profiles' => [self::MEDIA.';profile="urn:jsonapi:profile:rel-counts https://example.test/unknown"', 201];
    }
}
