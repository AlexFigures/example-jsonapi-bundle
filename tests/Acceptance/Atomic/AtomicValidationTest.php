<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicValidationTest extends AcceptanceTestCase
{
    #[ExpectedBundleGap('ATOMIC-003')]
    public function testRefCannotContainBothIdAndLid(): void
    {
        // EXPECTED_BUNDLE_GAP ATOMIC-003: id and lid are exclusive target alternatives.
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles') + ['lid' => 'local-1'], 'data' => $this->patchPayload(['title' => 'Invalid target'])['data']]]), 400, '/atomic:operations/0/ref');
    }

    #[DataProvider('invalidDocuments')]
    public function testMalformedAtomicDocument(array $document, string $pointer): void
    {
        $response = $this->requestJsonApi('POST', '/api/operations', $document, ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC]);
        $this->assertJsonApiError($response, 400, $pointer);
    }

    public static function invalidDocuments(): iterable
    {
        yield [['meta' => new \stdClass()], '/atomic:operations'];
        yield [['atomic:operations' => []], '/atomic:operations'];
        yield [['atomic:operations' => [['op' => 'invalid', 'ref' => ['type' => 'articles','id' => '1']]]], '/atomic:operations/0/op'];
        yield [['atomic:operations' => [['op' => 'remove']]], '/atomic:operations/0'];
        yield [['atomic:operations' => [['op' => 'remove', 'ref' => ['type' => 'articles','id' => '1'], 'href' => '/api/articles/1']]], '/atomic:operations/0'];
        yield [['atomic:operations' => [['op' => 'remove', 'ref' => ['type' => 'unknown','id' => '1']]]], '/atomic:operations/0/ref/type'];
    }

    #[DataProvider('hrefs')]
    public function testInvalidHref(string $href): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'remove', 'href' => $href]]), 400, '/atomic:operations/0/href');
    }

    #[DataProvider('hrefsBundleGaps')]
    #[ExpectedBundleGap('ATOMIC-006', ['#1'])]
    public function testInvalidHrefBundleGap(string $href): void
    {
        $this->assertJsonApiError($this->atomic([['op' => 'remove', 'href' => $href]]), 400, '/atomic:operations/0/href');
    }

    private static function hrefsAll(): iterable
    {
        yield ['/outside/articles/1']; yield ['/api/articles/1/trailing']; yield ['%broken']; yield ['https://other.example/api/articles/1'];
    }

    #[ExpectedBundleGap('ATOMIC-005')]
    public function testSameOriginAbsoluteHref(): void
    {
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'href' => 'http://localhost'.$this->url(), 'data' => $this->patchPayload(['title' => 'Absolute target'])['data']]]));
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Absolute target', $doc['data']['attributes']['title']);
    }

    #[ExpectedBundleGap('ATOMIC-005')]
    public function testRelativeUriReference(): void
    {
        $this->decodeJsonApi($this->atomic([['op' => 'update', 'href' => 'articles/'.$this->ids['article-1'], 'data' => $this->patchPayload(['title' => 'Relative target'])['data']]]));
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Relative target', $doc['data']['attributes']['title']);
    }
    #[ExpectedBundleGap('ATOMIC-010')]
    public function testAtomicClientIdPolicyMatchesOrdinaryCreates(): void
    {
        $data = $this->articlePayload()['data'];
        $data['id'] = '999999';
        $this->assertJsonApiError($this->atomic([['op' => 'add', 'href' => '/api/articles', 'data' => $data]]), 403);
    }

    #[ExpectedBundleGap('ATOMIC-009')]
    public function testAtomicReadonlyResourceCannotBeWritten(): void
    {
        $op = ['op' => 'update', 'ref' => $this->identifier('audit', 'audit-logs'), 'data' => ['type' => 'audit-logs', 'id' => $this->ids['audit'], 'attributes' => ['message' => 'Must not write']]];
        $this->assertJsonApiError($this->atomic([$op]), 403);
    }

    #[ExpectedBundleGap('ATOMIC-011')]
    public function testAtomicUnknownAttributeIsRejected(): void
    {
        $data = $this->patchPayload(['secret' => 'Must not write'])['data'];
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $data]]), 400, '/atomic:operations/0/data/attributes/secret');
    }

    #[ExpectedBundleGap('ATOMIC-011')]
    public function testAtomicFailurePointerIdentifiesOperationAndExternalField(): void
    {
        $data = $this->patchPayload(['title' => 'x'])['data'];
        $this->assertJsonApiError($this->atomic([['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $data]]), 422, '/atomic:operations/0/data/attributes/title');
    }

    #[ExpectedBundleGap('ATOMIC-007')]
    public function testEmptyAtomicResultsMustBeObjects(): void
    {
        $response = $this->atomic([['op' => 'remove', 'ref' => $this->identifier('article-1', 'articles')]]);
        $this->decodeJsonApi($response);
        $doc = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(\stdClass::class, $doc->{'atomic:results'}[0]);
    }


    public static function hrefs(): iterable
    {
        foreach (self::hrefsAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (!in_array($label, ['#1'], true)) { yield $label => $row; }
        }
    }

    public static function hrefsBundleGaps(): iterable
    {
        foreach (self::hrefsAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (in_array($label, ['#1'], true)) { yield $label => $row; }
        }
    }
}
