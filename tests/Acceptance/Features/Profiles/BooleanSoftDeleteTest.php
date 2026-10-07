<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\PgEntity\FeatureMemo;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use Doctrine\Persistence\ManagerRegistry;

final class BooleanSoftDeleteTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_soft_boolean'; }

    public function testBooleanStrategyExcludesOnlyMarkedRows(): void
    {
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureMemo::class);
        $alive = new FeatureMemo('Alive');
        $deleted = new FeatureMemo('Deleted');
        $deleted->deleted = true;
        $em->persist($alive);
        $em->persist($deleted);
        $em->flush();
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-memos', headers: ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:soft-delete"']));
        self::assertSame(['Alive'], array_column(array_column($doc['data'], 'attributes'), 'title'));
    }
}
