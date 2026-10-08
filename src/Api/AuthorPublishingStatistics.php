<?php

declare(strict_types=1);

namespace App\Api;

use AlexFigures\JsonApi\Resource\Attribute\{JsonApiResource, Id, Attribute};

/** Aggregate read model: no Doctrine entity, setters or generated mutations. */
#[JsonApiResource(type: 'author-publishing-statistics', operations: [])]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'authors.publishing-statistics', path: '/api/authors/{authorId}/publishing-statistics', methods: ['GET'], handler: \App\Application\Article\AuthorPublishingStatisticsHandler::class)]
final readonly class AuthorPublishingStatistics
{
    public function __construct(
        #[Id] public string $id,
        #[Attribute] public int $draftCount,
        #[Attribute] public int $publishedCount,
    ) {}
}
