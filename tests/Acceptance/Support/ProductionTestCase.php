<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

use App\PgEntity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

abstract class ProductionTestCase extends AcceptanceTestCase
{
    protected function environment(): string { return 'publishing'; }

    protected function asUser(string $user, string $method, string $url, array|string|null $body = null, array $headers = []): Response
    {
        return $this->requestJsonApi($method, $url, $body, $headers + ['Authorization' => 'Bearer '.$user]);
    }

    protected function storedArticle(string $key = 'article-1'): Article
    {
        $manager = self::getContainer()->get(ManagerRegistry::class)->getManager('pgsql');
        $manager->clear();
        return $manager->find(Article::class, (int) $this->ids[$key]);
    }

    protected function notificationCount(): int
    {
        return (int) self::getContainer()->get(ManagerRegistry::class)->getConnection('pgsql')
            ->fetchOne('SELECT COUNT(*) FROM publication_notifications');
    }
}
