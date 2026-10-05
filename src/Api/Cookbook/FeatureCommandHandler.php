<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use AlexFigures\Symfony\CustomRoute\Context\CustomRouteContext;
use AlexFigures\Symfony\CustomRoute\Handler\CustomRouteHandlerInterface;
use AlexFigures\Symfony\CustomRoute\Result\CustomRouteResult;
use Doctrine\Persistence\ManagerRegistry;

final class FeatureCommandHandler implements CustomRouteHandlerInterface
{
    public function __construct(private ManagerRegistry $doctrine)
    {
    }

    public function handle(CustomRouteContext $context): CustomRouteResult
    {
        return match ($context->getParam('form')) {
            'no-content' => CustomRouteResult::noContent(),
            'accepted' => CustomRouteResult::accepted($context->getResource()),
            'conflict' => CustomRouteResult::conflict('Application command conflict'),
            default => CustomRouteResult::resource($context->getResource())->withMeta(['transaction_active' => $this->doctrine->getConnection('pgsql')->isTransactionActive()]),
        };
    }
}
