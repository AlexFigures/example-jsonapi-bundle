<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Torture\Infrastructure\RequestMetrics;
use PHPUnit\Framework\Attributes\DataProvider;

final class IncludeLimitsTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_include_limits'; }
    #[DataProvider('boundaries')]
    public function testIncludeDepthAndPathBoundaries(string $path, int $status): void
    {
        RequestMetrics::start('');
        $response = $this->requestJsonApi('GET', '/api/categories?'.http_build_query(['include' => $path]));
        if ($status === 200) { $this->decodeJsonApi($response); }
        else {
            $this->assertJsonApiError($response, 400, parameter: 'include');
            self::assertCount(0, RequestMetrics::$queries, 'Over-limit include must fail before SQL.');
        }
    }
    public static function boundaries(): iterable
    {
        yield 'below depth' => ['children', 200];
        yield 'at depth' => ['children.children', 200];
        yield 'above depth' => ['children.children.children', 400];
        yield 'above paths' => ['children,parent', 400];
    }
    protected function tearDown(): void { RequestMetrics::$active = false; parent::tearDown(); }
}
