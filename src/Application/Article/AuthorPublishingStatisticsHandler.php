<?php

declare(strict_types=1);

namespace App\Application\Article;

use AlexFigures\JsonApi\CustomRoute\Attribute\NoTransaction;
use AlexFigures\JsonApi\CustomRoute\Context\CustomRouteContext;
use AlexFigures\JsonApi\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\JsonApi\CustomRoute\Result\CustomRouteResult;
use AlexFigures\JsonApi\Http\Exception\ForbiddenException;
use AlexFigures\JsonApi\Http\Exception\NotFoundException;
use App\Api\AuthorPublishingStatistics;
use App\PgEntity\Author;
use App\Security\PublishingContext;
use Doctrine\Persistence\ManagerRegistry;

#[NoTransaction]
final class AuthorPublishingStatisticsHandler implements CustomRouteHandlerInterface
{
    public function __construct(private ManagerRegistry $doctrine, private PublishingContext $user) {}

    public function handle(CustomRouteContext $context): CustomRouteResult
    {
        $id = (string) $context->getParam('authorId');
        if (!ctype_digit($id) || strlen($id) > 9) { throw new NotFoundException('Author not found.'); }
        $author = $this->doctrine->getManagerForClass(Author::class)->find(Author::class, (int) $id)
            ?? throw new NotFoundException('Author not found.');
        if ($this->user->enabled) {
            $identity = $this->user->identity();
            if ($identity->role !== 'admin' && $identity->authorEmail !== $author->getEmail()) {
                throw new ForbiddenException('Statistics belongs to another author.');
            }
        }
        $rows = $this->doctrine->getConnection('pgsql')->fetchAllAssociative(
            'SELECT status, COUNT(*) AS total FROM articles WHERE author_id = ? GROUP BY status', [$author->getId()]
        );
        $counts = array_column($rows, 'total', 'status');
        return CustomRouteResult::resource(new AuthorPublishingStatistics(
            (string) $author->getId(), (int) ($counts['draft'] ?? 0), (int) ($counts['published'] ?? 0)
        ));
    }
}
