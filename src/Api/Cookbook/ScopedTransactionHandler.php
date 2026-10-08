<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use AlexFigures\JsonApi\Contract\Tx\{TransactionManager, ScopedTransactionManagerInterface};
use AlexFigures\JsonApi\CustomRoute\Attribute\NoTransaction;
use AlexFigures\JsonApi\CustomRoute\Context\CustomRouteContext;
use AlexFigures\JsonApi\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\JsonApi\CustomRoute\Result\CustomRouteResult;
use App\PgEntity\FeatureArticle;
use App\MysqlEntity\Comment;
use Doctrine\Persistence\ManagerRegistry;

#[NoTransaction]
final class ScopedTransactionHandler implements CustomRouteHandlerInterface
{
    public function __construct(private TransactionManager $transactions, private ManagerRegistry $doctrine) {}

    public function handle(CustomRouteContext $context): CustomRouteResult
    {
        if (!$this->transactions instanceof ScopedTransactionManagerInterface) {
            return CustomRouteResult::conflict('Provider does not expose scoped transaction capability.');
        }
        $classes = $context->getParam('scope') === 'mixed' ? [FeatureArticle::class, Comment::class] : [FeatureArticle::class];
        return $this->transactions->transactionalFor($classes, function () use ($context): CustomRouteResult {
            return CustomRouteResult::resource($context->getResource())->withMeta([
                'pgsql_transaction' => $this->doctrine->getConnection('pgsql')->isTransactionActive(),
                'mysql_transaction' => $this->doctrine->getConnection('mysql')->isTransactionActive(),
            ]);
        });
    }
}
