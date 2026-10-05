<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use AlexFigures\Symfony\Contract\Tx\{TransactionManager, ScopedTransactionManagerInterface};
use AlexFigures\Symfony\CustomRoute\Attribute\NoTransaction;
use AlexFigures\Symfony\CustomRoute\Context\CustomRouteContext;
use AlexFigures\Symfony\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\Symfony\CustomRoute\Result\CustomRouteResult;
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
