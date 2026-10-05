<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Application\Article\ArticlePublished;
use App\PgEntity\PublicationNotification;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class PublicationRecorder
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    #[AsEventListener]
    public function record(ArticlePublished $event): void
    {
        $this->doctrine->getManagerForClass(PublicationNotification::class)->persist(
            new PublicationNotification($event->articleId, $event->publishedAt)
        );
    }
}
