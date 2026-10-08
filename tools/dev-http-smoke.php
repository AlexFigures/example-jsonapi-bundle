<?php

declare(strict_types=1);

// Exercises the actual development server, including dotted URLs and Runtime bootstrap.
$base = $argv[1] ?? 'http://127.0.0.1:8000';
foreach (['/_jsonapi/openapi.json', '/_jsonapi/schemas', '/_jsonapi/docs'] as $path) {
    $context = stream_context_create(['http' => ['timeout' => 20, 'header' => 'Accept: */*']]);
    $body = file_get_contents($base.$path, false, $context);
    if ($body === false || !str_contains($http_response_header[0] ?? '', ' 200 ')) {
        throw new RuntimeException('Documentation HTTP request failed: '.$path);
    }
    if ($path === '/_jsonapi/docs') {
        if (!str_contains($body, 'SwaggerUIBundle') || !str_contains($body, '/_jsonapi/openapi.json')) {
            throw new RuntimeException('Swagger shell does not reference the specification.');
        }
    } else {
        $document = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if ($path === '/_jsonapi/openapi.json' && (!str_starts_with($document['openapi'] ?? '', '3.') || empty($document['paths']))) {
            throw new RuntimeException('Invalid OpenAPI document.');
        }
        if ($path === '/_jsonapi/openapi.json' && isset($document['paths']['/cookbook/responses/{form}'])) {
            throw new RuntimeException('Test-only deprecated cookbook endpoint leaked into development docs.');
        }
        if ($path === '/_jsonapi/schemas' && empty($document['$defs'])) {
            throw new RuntimeException('Invalid JSON Schema document.');
        }
    }
    echo $path." OK\n";
}
