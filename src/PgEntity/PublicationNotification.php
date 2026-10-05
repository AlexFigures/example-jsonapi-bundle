<?php

declare(strict_types=1);

namespace App\PgEntity;

use Doctrine\ORM\Mapping as ORM;

/** Observable local side effect; deliberately not an API resource or external delivery. */
#[ORM\Entity]
#[ORM\Table(name: 'publication_notifications')]
final class PublicationNotification
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    public ?int $id = null;

    public function __construct(
        #[ORM\Column(unique: true)] public int $articleId,
        #[ORM\Column(type: 'datetime_immutable')] public \DateTimeImmutable $publishedAt,
    ) {
    }
}
