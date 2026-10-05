<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Cache;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;

final class LastModifiedConfigurationTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_lastmodified'; }

    public function testConfiguredFieldAndCollectionMaximumDetermineValidators(): void
    {
        $db = self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql');
        $db->executeStatement("UPDATE articles SET created_at = '2020-01-01 00:00:00', updated_at = '2025-01-01 00:00:00'");
        $db->executeStatement("UPDATE articles SET created_at = '2021-01-01 00:00:00' WHERE id = ?", [$this->ids['article-1']]);
        $get = $this->requestJsonApi('GET', $this->url());
        $this->decodeJsonApi($get);
        self::assertSame('Fri, 01 Jan 2021 00:00:00 GMT', $get->headers->get('Last-Modified'));
        $collection = $this->requestJsonApi('GET', '/api/articles?sort=id');
        $this->decodeJsonApi($collection);
        self::assertSame($get->headers->get('Last-Modified'), $collection->headers->get('Last-Modified'));
        $conditional = $this->requestJsonApi('GET', $this->url(), headers: ['If-Modified-Since' => $get->headers->get('Last-Modified')]);
        self::assertSame(304, $conditional->getStatusCode());
        self::assertSame('', $conditional->getContent());
    }
}
