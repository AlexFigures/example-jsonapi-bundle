<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class IdentifierBudgetTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_identifier_budget'; }

    #[DataProvider('paths')]
    public function testOversizedRelationshipIsRejectedWithoutTruncation(string $suffix): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url().$suffix), 400);
    }

    public static function paths(): iterable
    {
        yield 'root linkage' => [''];
        yield 'included relationship' => ['?include=tags'];
        yield 'linkage endpoint' => ['/relationships/tags'];
    }
}
