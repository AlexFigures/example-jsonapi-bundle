<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\JsonApi\Http\Response\JsonApiResponseFactory;
use AlexFigures\JsonApi\Http\Error\ErrorBuilder;
use AlexFigures\JsonApi\Http\Exception\JsonApiHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Application selects problem documentation; the bundle owns error serialization. */
final class ErrorLinksController
{
    public const TYPE = 'https://example.test/problems/publishing-conditions';
    public const ABOUT = 'https://example.test/incidents/publication-42';

    #[Route('/api/cookbook/error-links/{variant}', methods: ['GET'])]
    public function __invoke(string $variant, JsonApiResponseFactory $responses, ErrorBuilder $errors): Response
    {
        if ($variant === 'both') {
            throw new JsonApiHttpException(422, 'Publishing conditions are not met.', errors: [
                $errors->create('422', 'publishing-conditions', 'Publishing conditions',
                    'Publishing conditions are not met.', aboutLink: self::ABOUT, typeLink: self::TYPE),
            ]);
        }
        $error = $variant === 'multiple'
            ? $responses->validationErrors([
                ['pointer' => '/data/attributes/title', 'detail' => 'Title is required.'],
                ['pointer' => '/data/attributes/content', 'detail' => 'Content is required.'],
            ])
            : $responses->error(422, 'Publishing conditions are not met.')
                ->withCode('publishing-conditions')->withTitle('Publishing conditions');

        if ($variant !== 'omitted') {
            $error = $error->withTypeLink(self::TYPE);
        }

        return $error->build();
    }
}
