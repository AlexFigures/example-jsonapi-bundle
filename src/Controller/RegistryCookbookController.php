<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\JsonApi\Resource\Registry\{ResourceRegistryInterface, CustomRouteRegistryInterface};
use AlexFigures\JsonApi\Http\Response\JsonApiResponseFactory;
use App\PgEntity\Author;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Attribute\Route;

/** Example: application diagnostics consume the public registries through DI. */
final readonly class RegistryCookbookController
{
    public function __construct(private \AlexFigures\JsonApi\Profile\ProfileRegistry $profiles, private ResourceRegistryInterface $resources, private CustomRouteRegistryInterface $routes, private JsonApiResponseFactory $responses, private ManagerRegistry $doctrine) {}
    #[Route('/cookbook/resource-registry', name: 'cookbook.resource_registry', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $meta = $this->resources->getByType('feature-articles');
        $routes = $this->routes->getByResourceType('feature-articles');
        $options = array_values(array_filter($routes, static fn ($route): bool => $route->name === 'cookbook.options'))[0];
        $author = $this->doctrine->getRepository(Author::class)->findOneBy(['email' => 'ada@example.test']);
        return $this->responses->resource('authors', $author)->withRequest($request)->withMeta([
            'metadata_contract' => $meta instanceof \AlexFigures\JsonApi\Contract\Resource\ResourceMetadataInterface,
            'profile_descriptor' => $this->profiles->descriptors()['urn:example:profile:cookbook'],
            'has_type' => $this->resources->hasType('feature-articles'),
            'class_type' => $this->resources->getByClass(\App\PgEntity\FeatureMemo::class)?->type,
            'primary_class_type' => $this->resources->getByClass(\App\PgEntity\FeatureArticle::class)?->type,
            'all_types' => array_map(static fn ($resource): string => $resource->type, $this->resources->all()),
            'route_names' => array_map(static fn ($route): string => $route->name, $this->routes->all()),
            'resource_route_names' => array_map(static fn ($route): string => $route->name, $routes),
            'description' => $options->description,
            'source_policy' => $meta->relationships['source']->linkingPolicy->value,
        ])->build();
    }
}
