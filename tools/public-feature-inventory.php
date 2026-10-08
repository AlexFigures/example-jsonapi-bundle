<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use AlexFigures\JsonApi\Bridge\Symfony\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ArrayNode;

$features = [];
$visit = function (object $node, string $path) use (&$visit, &$features): void {
    if ($node instanceof ArrayNode && $node->getChildren() !== []) {
        foreach ($node->getChildren() as $name => $child) {
            $visit($child, $path.'.'.$name);
        }
        return;
    }
    if ($node instanceof \Symfony\Component\Config\Definition\PrototypedArrayNode) {
        $visit($node->getPrototype(), $path.'.*');
        return;
    }
    $features[] = ['feature' => $path, 'public_api' => 'Configuration', 'kind' => 'configuration'];
};
$visit((new Configuration())->getConfigTreeBuilder()->buildTree(), 'jsonapi');
$bundle = dirname(__DIR__).'/vendor/alexfigures/symfony-jsonapi-bundle';
foreach (['Resource/Attribute', 'Docs/Attribute', 'Contract/Data', 'Contract/Resource', 'Contract/Tx', 'Profile/Hook', 'Profile/Attribute', 'Bridge/Symfony/Routing/Attribute', 'Events', 'Resource/Metadata', 'Filter/Operator', 'Filter/Handler', 'CustomRoute/Handler', 'CustomRoute/Attribute', 'Resource/Definition', 'Resource/Mapper', 'Resource/Registry'] as $directory) {
    foreach (glob($bundle.'/src/'.$directory.'/*.php') as $file) {
        $short = basename($file, '.php');
        if ($directory === 'Resource/Metadata' && $short !== 'RelationshipLinkingPolicy') { continue; }
        $class = 'AlexFigures\\JsonApi\\'.str_replace('/', '\\', $directory).'\\'.$short;
        if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) { continue; }
        $reflection = new ReflectionClass($class);
        $features[] = ['feature' => $directory.'/'.$short, 'public_api' => $class, 'kind' => 'public_type'];
        if (str_contains($directory, 'Attribute') && $reflection->getConstructor() !== null) {
            foreach ($reflection->getConstructor()->getParameters() as $parameter) {
                $features[] = ['feature' => $directory.'/'.$short.'::$'.$parameter->getName(), 'public_api' => $class, 'kind' => 'constructor_option'];
            }
        }
        if ($reflection->isEnum()) {
            foreach ((new ReflectionEnum($class))->getCases() as $case) {
                $features[] = ['feature' => $directory.'/'.$short.'::'.$case->getName(), 'public_api' => $class, 'kind' => 'enum_value'];
            }
        }
    }
}
foreach (['Profile/ProfileInterface', 'CustomRoute/Context/CustomRouteContext', 'CustomRoute/Result/CustomRouteResult', 'CustomRoute/Query/CriteriaBuilder', 'Http/Response/JsonApiResponseFactory', 'Http/Response/JsonApiResponseBuilder'] as $name) {
    $class = 'AlexFigures\\JsonApi\\'.str_replace('/', '\\', $name);
    foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isConstructor() || str_starts_with($method->getName(), 'get')) { continue; }
        $features[] = ['feature' => $name.'::'.$method->getName(), 'public_api' => $class, 'kind' => 'public_method'];
    }
}
$tags = [];
$features[] = [
    'feature' => 'Http/Response/JsonApiErrorBuilder::withTypeLink',
    'public_api' => \AlexFigures\JsonApi\Http\Response\JsonApiErrorBuilder::class,
    'kind' => 'public_method',
];
foreach (glob($bundle.'/config/*.php') as $file) {
    preg_match_all("/tagged_(?:iterator|locator)\\('([^']+)'/", file_get_contents($file), $matches);
    foreach ($matches[1] as $tag) { $tags[$tag] = true; }
}
foreach (array_keys($tags) as $tag) {
    $features[] = ['feature' => 'ServiceTag/'.$tag, 'public_api' => 'Symfony tagged service registration', 'kind' => 'service_tag'];
}
$output = ['bundle_revision' => Composer\InstalledVersions::getReference('alexfigures/symfony-jsonapi-bundle'), 'features' => $features];
file_put_contents(dirname(__DIR__).'/docs/public-feature-inventory.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
echo count($features), " public surface entries inventoried\n";
