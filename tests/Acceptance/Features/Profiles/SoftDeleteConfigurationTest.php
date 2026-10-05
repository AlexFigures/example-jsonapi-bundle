<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class SoftDeleteConfigurationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_soft_'.$this->dataName(); }
    private function headers(): array { return ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:soft-delete"']; }

    #[DataProvider('visibility')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('PROFILE-SOFT-VISIBILITY', ['include', 'only'])]
    public function testConfiguredVisibility(int $count): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/categories?page[size]=20', headers: $this->headers()));
        self::assertCount($count, $doc['data']);
    }

    public static function visibility(): iterable
    {
        yield 'exclude' => [3];
        yield 'include' => [4];
        yield 'only' => [1];
    }

    #[DataProvider('deleteSemantics')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('PROFILE-SOFT-DELETE-SEMANTICS', ['soft'])]
    public function testDeleteSemanticsPersistExpectedState(bool $rowRemains): void
    {
        $response = $this->requestJsonApi('DELETE', '/api/categories/'.$this->ids['leaf'], headers: $this->headers());
        self::assertSame(204, $response->getStatusCode(), (string) $response->getContent());
        $db = self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql');
        $row = $db->fetchAssociative('SELECT deleted_at FROM categories WHERE id = ?', [$this->ids['leaf']]);
        self::assertSame($rowRemains, $row !== false);
        if ($rowRemains) { self::assertNotNull($row['deleted_at']); }
    }

    public static function deleteSemantics(): iterable
    {
        yield 'soft' => [true];
        yield 'hard' => [false];
    }

    #[DataProvider('flags')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('PROFILE-SOFT-QUERY-FLAGS')]
    public function testConfiguredQueryFlagIsConsumedBeforeWhitelist(string $flag, int $count): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/categories?'.http_build_query(['filter' => [$flag => 'true'], 'page' => ['size' => 20]]), headers: $this->headers()));
        self::assertCount($count, $doc['data']);
    }

    public static function flags(): iterable
    {
        yield 'flags' => ['includeArchived', 4];
    }
}
