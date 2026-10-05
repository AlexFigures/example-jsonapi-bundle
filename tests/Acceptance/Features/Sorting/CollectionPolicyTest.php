<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Sorting;

use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class CollectionPolicyTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features';
    }

    public function testAmbiguousInheritedToManySortIsRejected(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/feature-articles?sort=tags.name'), 400);
    }
}
