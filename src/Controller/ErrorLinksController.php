<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\Symfony\Http\Response\JsonApiResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Application selects problem documentation; the bundle owns error serialization. */
final class ErrorLinksController
{
    public const TYPE = 'https://example.test/problems/publishing-conditions';
    public const ABOUT = 'https://example.test/incidents/publication-42';

    #[Route('/api/cookbook/error-links/{variant}', methods: ['GET'])]
    public function __invoke(string $variant, JsonApiResponseFactory $responses): Response
    {
        $error = $variant === 'multiple'
            ? $responses->validationErrors([
                ['pointer' => '/data/attributes/title', 'detail' => 'Title is required.'],
                ['pointer' => '/data/attributes/content', 'detail' => 'Content is required.'],
            ])
            : $responses->error(422, 'Publishing conditions are not met.')
                ->withCode('publishing-conditions')->withTitle('Publishing conditions');

        if ($variant !== 'omitted') {
            $error = $error->withLinks(['type' => self::TYPE]);
        }
        if ($variant === 'both') {
            $error = $error->withLinks(['about' => self::ABOUT]);
        }

        return $error->build();
    }
}
