<?php

declare(strict_types=1);

// One fresh process and Doctrine connection per concurrent HTTP client.
putenv('APP_ENV=publishing');
putenv('APP_DEBUG=0');
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'publishing';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0';
require dirname(__DIR__).'/tests/bootstrap.php';
$input = json_decode(trim((string) fgets(STDIN)), true, 512, JSON_THROW_ON_ERROR);
$kernel = new App\Kernel('publishing', false);
$client = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
$kernel->boot();
fwrite(STDOUT, "READY\n");
fflush(STDOUT);
if (trim((string) fgets(STDIN)) !== 'GO') { throw new RuntimeException('Missing concurrent-start barrier'); }
$server = ['CONTENT_TYPE' => 'application/vnd.api+json', 'HTTP_ACCEPT' => 'application/vnd.api+json'];
foreach ($input['headers'] ?? [] as $name => $value) {
    $key = strtoupper(str_replace('-', '_', $name));
    $server[$key === 'CONTENT_TYPE' ? $key : 'HTTP_'.$key] = $value;
}
$body = isset($input['body']) ? json_encode($input['body'], JSON_THROW_ON_ERROR) : null;
$client->request($input['method'], $input['url'], server: $server, content: $body);
$response = $client->getResponse();
fwrite(STDOUT, json_encode(['status' => $response->getStatusCode(), 'body' => json_decode((string) $response->getContent(), true),
    'content_type' => $response->headers->get('Content-Type')], JSON_THROW_ON_ERROR)."\n");
