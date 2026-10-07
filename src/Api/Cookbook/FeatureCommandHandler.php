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
            'created' => CustomRouteResult::created($context->getResource()),
            'bad-request' => CustomRouteResult::badRequest('Application input rejected'),
            'forbidden' => CustomRouteResult::forbidden('Application access denied'),
            'not-found' => CustomRouteResult::notFound('Application item missing'),
            'unprocessable' => CustomRouteResult::unprocessable([['pointer' => '/data/attributes/title', 'detail' => 'Title rejected']]),
            'modifiers' => CustomRouteResult::resource($context->getResource())->withMeta(['first' => true])->withMeta(['second' => true])->withLinks(['help' => '/cookbook/help'])->withStatus(203)->withHeader('X-Cookbook-Result', 'yes'),
            'checks' => CustomRouteResult::resource($context->getResource())->withMeta([
                'resource' => CustomRouteResult::resource($context->getResource())->isResource(),
                'collection' => CustomRouteResult::collection([])->isCollection(),
                'error' => CustomRouteResult::badRequest('invalid')->isError(),
                'empty' => CustomRouteResult::noContent()->isNoContent(),
            ]),
            'conflict' => CustomRouteResult::conflict('Application command conflict'),
            default => CustomRouteResult::resource($context->getResource())->withMeta(['transaction_active' => $this->doctrine->getConnection('pgsql')->isTransactionActive()]),
        };
    }
}
