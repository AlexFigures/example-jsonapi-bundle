<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use AlexFigures\Symfony\CustomRoute\Attribute\NoTransaction;
use AlexFigures\Symfony\CustomRoute\Context\CustomRouteContext;
use AlexFigures\Symfony\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\Symfony\CustomRoute\Result\CustomRouteResult;
use Doctrine\Persistence\ManagerRegistry;

#[NoTransaction]
final class FeatureReadHandler implements CustomRouteHandlerInterface
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function handle(CustomRouteContext $context): CustomRouteResult
    {
        $transaction = $this->doctrine->getConnection('pgsql')->isTransactionActive();
        if ($context->hasResource()) {
            return CustomRouteResult::resource($context->getResource())->withMeta(['transaction_active' => $transaction]);
        }
        $criteria = $context->criteria()->addFilter('title', 'eq', $context->getQueryParam('title', 'Alpha'))
            ->addCustomCondition(static function (object $qb): void {
                $root = $qb->getRootAliases()[0];
                $qb->andWhere("$root.id > :cookbook_min_id")->setParameter('cookbook_min_id', 0);
            })->build();
        $slice = $context->getRepository()->findCollection('feature-articles', $criteria);
        return CustomRouteResult::collection($slice->items, $slice->totalItems)->withMeta(['transaction_active' => $transaction]);
    }
}
