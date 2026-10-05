<?php

declare(strict_types=1);

namespace App\Application\Article;

final class PublicationRejected extends \DomainException
{
    public function __construct(string $message, public readonly int $status)
    {
        parent::__construct($message);
    }
}
