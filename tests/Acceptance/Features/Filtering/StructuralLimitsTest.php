<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StructuralLimitsTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features';
    }

    #[DataProvider('limits')]
    public function testStructuralGuards(array $filter, int $status): void
    {
        \App\Torture\Infrastructure\RequestMetrics::start('');
        $response = $this->requestJsonApi('GET', '/api/articles?'.http_build_query(['filter' => $filter]));
        if ($status === 400) {
            $this->assertJsonApiError($response, 400);
            self::assertCount(0, \App\Torture\Infrastructure\RequestMetrics::$queries, 'Rejected filters must execute no SQL.');
        } else {
            $this->decodeJsonApi($response);
        }
    }

    public function testWeightedRelationshipHopComplexityBoundary(): void
    {
        foreach ([16 => 200, 17 => 400] as $size => $status) {
            \App\Torture\Infrastructure\RequestMetrics::start('');
            $response = $this->requestJsonApi('GET', '/api/articles?'.http_build_query(['filter' => ['author.name' => 'Ada Lovelace'], 'page' => ['size' => $size]]));
            self::assertSame($status, $response->getStatusCode(), (string) $response->getContent());
            if ($status === 400) {
                self::assertCount(0, \App\Torture\Infrastructure\RequestMetrics::$queries);
            }
        }
    }

    protected function tearDown(): void
    {
        \App\Torture\Infrastructure\RequestMetrics::$active = false;
        parent::tearDown();
    }

    public static function limits(): iterable
    {
        yield 'operands below' => [['views' => ['in' => [10, 20, 30]]], 200];
        yield 'operands at' => [['views' => ['in' => [10, 20, 30, 40]]], 200];
        yield 'operands above' => [['views' => ['in' => [10, 20, 30, 40, 50]]], 400];
        yield 'nin above' => [['views' => ['nin' => [10, 20, 30, 40, 50]]], 400];
        yield 'nodes at' => [['or' => [['views' => 10], ['views' => 20], ['views' => 30], ['views' => 40]]], 200];
        yield 'nodes above' => [['or' => [['published-at' => ['isnull' => true]], ['published-at' => ['isnull' => false]], ['published-at' => ['isnull' => true]], ['published-at' => ['isnull' => false]], ['published-at' => ['isnull' => true]]]], 400];
        yield 'depth below' => [['and' => [['views' => 10], ['title' => ['like' => 'title']]]], 200];
        yield 'depth at' => [['and' => [['or' => [['views' => 10], ['views' => 20]]], ['title' => ['like' => 'title']]]], 200];
        yield 'depth above' => [['and' => [['or' => [['and' => [['views' => 10], ['views' => 20]]]]]]], 400];
    }
}
