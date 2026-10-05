<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class NativeOperatorsTest extends AcceptanceTestCase
{
    #[DataProvider('nullOperators')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('FILTER-PUBLIC-NULL-NAMES')]
    public function testDocumentedNullOperatorNames(string $operator, array $sequences): void
    {
        $doc = $this->collection(['filter' => ['published-at' => [$operator => 'true']], 'sort' => 'views', 'page' => ['size' => 20]]);
        self::assertSame($sequences, array_column(array_column(array_column($doc['data'], 'attributes'), 'metadata'), 'sequence'));
    }

    public static function nullOperators(): iterable
    {
        yield 'null' => ['null', [1, 3, 5, 7, 9, 11]];
        yield 'nnull' => ['nnull', [2, 4, 6, 8, 10, 12]];
    }

    public function testUnsupportedNotExpressionHasControlledClientDiagnostic(): void
    {
        $doc = $this->assertJsonApiError($this->requestJsonApi('GET', '/api/articles?'.http_build_query(['filter' => ['not' => ['views' => 10]]])), 400);
        self::assertStringNotContainsString('SQLSTATE', json_encode($doc));
    }
}
