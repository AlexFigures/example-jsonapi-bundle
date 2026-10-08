<?php

declare(strict_types=1);

// Run after dev-setup.sh against a disposable demo database; persists a publishing journey.
$base = $argv[1] ?? 'http://127.0.0.1:8000';
$request = static function (string $method, string $path, ?array $body = null, string $actor = 'admin', bool $atomic = false) use ($base): array {
    $media = 'application/vnd.api+json'.($atomic ? ';ext="https://jsonapi.org/ext/atomic"' : '');
    $context = stream_context_create(['http' => [
        'method' => $method, 'timeout' => 20, 'ignore_errors' => true,
        'header' => "Accept: $media\r\nContent-Type: $media\r\nAuthorization: Bearer $actor\r\n",
        'content' => $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR),
    ]]);
    $response = file_get_contents($base.$path, false, $context);
    preg_match('/^HTTP\/\S+ (\d+)/', $http_response_header[0] ?? '', $matches);
    $status = (int) ($matches[1] ?? 0);
    if (!in_array($status, [200, 201], true) || $response === false) {
        throw new RuntimeException("$method $path returned $status: ".$response);
    }
    echo "$method $path $status\n";
    return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
};
$articles = $request('GET', '/api/articles?include=author,tags&fields[articles]=title,author,tags&sort=title,id&page[size]=2', actor: 'reader');
if (empty($articles['data'])) { throw new RuntimeException('Demo fixtures were not seeded.'); }
$request('GET', '/api/articles?filter[search][eq]=Article&sort=title-length,id', actor: 'reader');
$suffix = bin2hex(random_bytes(6));
$request('POST', '/api/authors', ['data' => ['type' => 'authors', 'attributes' => ['name' => 'Guide Author', 'email' => 'guide-'.$suffix.'@example.test']]]);
$scoped = $request('GET', '/api/articles?sort=id&page[size]=1', actor: 'editor-a');
$id = $scoped['data'][0]['id'];
$request('PATCH', '/api/articles/'.$id, ['data' => ['type' => 'articles', 'id' => $id, 'attributes' => ['content' => 'Guide publishing journey']]], 'editor-a');
$published = $request('POST', '/api/articles/'.$id.'/publish', actor: 'editor-a');
if (($published['data']['attributes']['status'] ?? '') !== 'published') { throw new RuntimeException('Publication did not transition the resource.'); }
$request('POST', '/api/operations', ['atomic:operations' => [['op' => 'add', 'data' => ['type' => 'authors', 'attributes' => ['name' => 'Atomic Guide Author', 'email' => 'atomic-guide-'.$suffix.'@example.test']]]]], atomic: true);
echo "Guide HTTP journey OK\n";
