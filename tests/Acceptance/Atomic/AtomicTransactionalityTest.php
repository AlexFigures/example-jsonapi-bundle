<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class AtomicTransactionalityTest extends AcceptanceTestCase
{
    #[DataProvider('failures')]
    public function testEarlierCreateIsRolledBack(string $failure, int $status): void
    {
        $first = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Must roll back'])['data']];
        $second = match ($failure) {
            'validation' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles'), 'data' => $this->patchPayload(['title' => 'x'], 'article-2')['data']],
            'unique' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles'), 'data' => $this->patchPayload(['slug' => 'article-01'], 'article-2')['data']],
            'unknown resource' => ['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => '999999']],
            'invalid relationship' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles') + ['relationship' => 'author'], 'data' => ['type' => 'authors', 'id' => '999999']],
        };
        $response = $this->atomic([$first, $second]);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Shared title', $doc['data']['attributes']['title'], 'Every preceding operation must roll back.');
        $this->assertJsonApiError($response, $status);
    }

    #[DataProvider('failuresBundleGaps')]
    #[ExpectedBundleGap('DOCTRINE-001', ['#1'])]
    public function testEarlierCreateIsRolledBackBundleGap(string $failure, int $status): void
    {
        $first = ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Must roll back'])['data']];
        $second = match ($failure) {
            'validation' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles'), 'data' => $this->patchPayload(['title' => 'x'], 'article-2')['data']],
            'unique' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles'), 'data' => $this->patchPayload(['slug' => 'article-01'], 'article-2')['data']],
            'unknown resource' => ['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => '999999']],
            'invalid relationship' => ['op' => 'update', 'ref' => $this->identifier('article-2', 'articles') + ['relationship' => 'author'], 'data' => ['type' => 'authors', 'id' => '999999']],
        };
        $response = $this->atomic([$first, $second]);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Shared title', $doc['data']['attributes']['title'], 'Every preceding operation must roll back.');
        $this->assertJsonApiError($response, $status);
    }

    private static function failuresAll(): iterable
    {
        yield ['validation',422]; yield ['unique',409]; yield ['unknown resource',404]; yield ['invalid relationship',404];
    }

    #[ExpectedBundleGap('ATOMIC-008')]
    public function testSuccessfulBatchOrderAndMixedEmptyResults(): void
    {
        $ops = [
            ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'First version'])['data']],
            ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'Final version'])['data']],
            ['op' => 'remove', 'ref' => $this->identifier('article-12', 'articles')],
        ];
        $doc = $this->decodeJsonApi($this->atomic($ops));
        self::assertCount(3, $doc['atomic:results']);
        self::assertSame('First version', $doc['atomic:results'][0]['data']['attributes']['title']);
        self::assertSame('Final version', $doc['atomic:results'][1]['data']['attributes']['title']);
        self::assertSame([], $doc['atomic:results'][2]);
        $after = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertSame('Final version', $after['data']['attributes']['title']);
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url('article-12')), 404);
    }
    public function testMixedManagerBatchRollsBackBeforeCommit(): void
    {
        $ops = [
            ['op' => 'update', 'ref' => $this->identifier('comment', 'comments'), 'data' => ['type' => 'comments', 'id' => $this->ids['comment'], 'attributes' => ['body' => 'Must roll back']]],
            ['op' => 'update', 'ref' => $this->identifier('article-1', 'articles'), 'data' => $this->patchPayload(['title' => 'x'])['data']],
        ];
        $response = $this->atomic($ops);
        $doc = $this->collection(['page' => ['size' => 20]], 'comments');
        self::assertCount(1, $doc['data']);
        self::assertSame('Useful article', $doc['data'][0]['attributes']['body']);
        $this->assertJsonApiError($response, 422);
    }


    public static function failures(): iterable
    {
        foreach (self::failuresAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (!in_array($label, ['#1'], true)) { yield $label => $row; }
        }
    }

    public static function failuresBundleGaps(): iterable
    {
        foreach (self::failuresAll() as $key => $row) {
            $label = is_int($key) ? '#'.$key : $key;
            if (in_array($label, ['#1'], true)) { yield $label => $row; }
        }
    }
}
