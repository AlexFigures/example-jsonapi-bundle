<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\Symfony\Docs\Attribute\{OpenApiEndpoint, OpenApiParameter, OpenApiRequestBody, OpenApiResponse, OpenApiHeader, OpenApiExample};
use AlexFigures\Symfony\Http\Response\JsonApiResponseFactory;
use App\PgEntity\Author;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FeatureCookbookController
{
    public function __construct(private JsonApiResponseFactory $responses, private ManagerRegistry $doctrine)
    {
    }

    #[Route('/cookbook/errors/{kind}', name: 'cookbook.errors', methods: ['GET'])]
    public function applicationError(string $kind): Response
    {
        if ($kind === 'client') { throw new \AlexFigures\Symfony\Http\Exception\BadRequestException('Application input rejected.'); }
        throw new \RuntimeException('Cookbook private diagnostic.');
    }

    #[Route('/cookbook/cache-version/{version}', name: 'cookbook.cache_version', methods: ['GET'])]
    public function version(string $version, Request $request): Response
    {
        $author = $this->doctrine->getRepository(Author::class)->findOneBy(['email' => 'ada@example.test']);
        $builder = $this->responses->resource('authors', $author)->withRequest($request);
        if ($version !== 'absent') { $builder = $builder->withHeader('X-Resource-Version', $version); }
        return $builder->build();
    }

    #[Route('/cookbook/responses/{form}', name: 'cookbook.responses', methods: ['GET', 'POST'])]
    #[OpenApiEndpoint(
        summary: 'Response factory cookbook',
        description: 'Application controller using the public JSON:API response factory.',
        operationId: 'cookbookResponse',
        tags: ['Cookbook'],
        parameters: [
            new OpenApiParameter('form', 'path', required: true),
            new OpenApiParameter('include', 'query'),
            new OpenApiParameter('X-Cookbook', 'header'),
        ],
        requestBody: new OpenApiRequestBody('application/vnd.api+json', ['type' => 'object']),
        responses: [200 => new OpenApiResponse('Author representation', contentType: 'application/vnd.api+json', schemaRef: '#/components/schemas/AuthorsResource', headers: ['X-Cookbook' => new OpenApiHeader('Example header')])],
        security: [['bearerAuth' => []]],
        deprecated: true,
        examples: ['sample' => new OpenApiExample('Example input', ['data' => ['type' => 'authors']])],
    )]
    public function response(string $form, Request $request): Response
    {
        if ($form === 'no-content') {
            return $this->responses->noContent();
        }
        if ($form === 'error') {
            return $this->responses->error(409, 'Application conflict')->build();
        }
        if ($form === 'validation') {
            return $this->responses->validationErrors([['pointer' => '/data/attributes/name', 'detail' => 'Name required']])->build();
        }
        $author = $this->doctrine->getRepository(Author::class)->findOneBy(['email' => 'ada@example.test']);
        if ($form === 'modifiers') {
            return $this->responses->collection('authors', [$author])->withRequest($request)
                ->withMeta(['cookbook' => true])->withLinks(['help' => 'https://example.test/help'])
                ->withHeader('X-Cookbook', 'yes')->withStatus(203)->withInclude(['articles'])
                ->withSparseFieldsets(['authors' => ['name', 'articles'], 'articles' => ['title']])
                ->withTotalItems(1)->build();
        }
        $builder = match ($form) {
            'created' => $this->responses->created('authors', $author),
            'collection' => $this->responses->collection('authors', [$author]),
            'accepted' => $this->responses->accepted('authors', $author),
            default => $this->responses->resource('authors', $author),
        };
        return $builder->withMeta(['cookbook' => true])->withHeader('X-Cookbook', 'yes')->withRequest($request)->build();
    }
}
