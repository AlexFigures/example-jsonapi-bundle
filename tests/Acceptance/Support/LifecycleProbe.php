<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

use AlexFigures\Symfony\Events\ResourceChangedEvent;
use App\Application\Article\ArticlePublished;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/** Test observer only: a separate connection sees committed database state. */
final class LifecycleProbe
{
    public array $domain = [];
    public array $resource = [];

    public function __construct(private ManagerRegistry $doctrine) {}

    #[AsEventListener]
    public function domain(ArticlePublished $event): void
    {
        $this->domain[] = $this->snapshot($event->articleId);
    }

    #[AsEventListener]
    public function resource(ResourceChangedEvent $event): void
    {
        if ($event->type === 'articles') {
            $this->resource[] = $this->snapshot((int) $event->id) + ['operation' => $event->operation];
        }
    }

    private function snapshot(int $id): array
    {
        $connection = $this->doctrine->getConnection('pgsql');
        $observer = DriverManager::getConnection($connection->getParams());
        try {
            return ['transaction_active' => $connection->isTransactionActive(),
                'committed_status' => $observer->fetchOne('SELECT status FROM articles WHERE id = ?', [$id]),
                'notification_count' => (int) $observer->fetchOne('SELECT COUNT(*) FROM publication_notifications WHERE article_id = ?', [$id])];
        } finally { $observer->close(); }
    }
}
