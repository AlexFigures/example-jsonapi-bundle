<?php

declare(strict_types=1);

namespace App\Application\Article;

use AlexFigures\JsonApi\CustomRoute\Context\CustomRouteContext;
use AlexFigures\JsonApi\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\JsonApi\CustomRoute\Result\CustomRouteResult;
use App\PgEntity\Article;
use App\Security\ArticlePolicy;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class PublishArticle implements CustomRouteHandlerInterface
{
    public function __construct(private ArticlePolicy $policy, private ManagerRegistry $doctrine, private EventDispatcherInterface $events)
    {
    }

    public function handle(CustomRouteContext $context): CustomRouteResult
    {
        $article = $context->getResource();
        if (!$article instanceof Article) {
            throw new \LogicException('Publish requires an Article.');
        }
        $this->policy->authorize($article, 'publish');
        try {
            if ($article->publish(new \DateTimeImmutable())) {
                // Application event, inside the bundle transaction. The recorder uses the same DB.
                $this->events->dispatch(new ArticlePublished((int) $article->getId(), $article->getPublishedAt()));
            }
        } catch (PublicationRejected $error) {
            return $error->status === 409 ? CustomRouteResult::conflict($error->getMessage()) : CustomRouteResult::unprocessable([['pointer' => '/data/attributes/content', 'detail' => $error->getMessage()]]);
        }
        $this->doctrine->getManagerForClass(Article::class)->flush();

        return CustomRouteResult::resource($article);
    }
}
