<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\Symfony\Http\Response\JsonApiResponseFactory;
use Doctrine\Persistence\ManagerRegistry;
use App\PgEntity\FeatureArticle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[\Symfony\Component\HttpKernel\Attribute\AsController]
final readonly class FeatureLegacyController
{
    public function __construct(private JsonApiResponseFactory $responses, private ManagerRegistry $doctrine) {}
    public function __invoke(Request $request): Response
    {
        $article = $this->doctrine->getRepository(FeatureArticle::class)->find((int) $request->attributes->get('id'));
        return $this->responses->resource('feature-articles', $article)->withRequest($request)->build();
    }
}
