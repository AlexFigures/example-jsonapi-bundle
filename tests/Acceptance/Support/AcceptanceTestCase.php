<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

use App\DataFixtures\AcceptanceFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AcceptanceTestCase extends WebTestCase
{
    protected const MEDIA = 'application/vnd.api+json';
    protected const ATOMIC = self::MEDIA.';ext="https://jsonapi.org/ext/atomic"';
    protected KernelBrowser $client;
    /** @var array<string, string> */
    protected array $ids;
    private array $httpTrace = [];


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(['environment' => $this->environment(), 'debug' => false]);
        $this->ids = AcceptanceFixtures::reset(self::getContainer()->get(ManagerRegistry::class));
    }

    protected function environment(): string
    {
        return 'test';
    }

    protected function url(string $key = 'article-1', string $type = 'articles'): string
    {
        return '/api/'.$type.'/'.$this->ids[$key];
    }

    /** @param array<string, mixed>|string|null $body @param array<string, string|null> $headers */
    protected function requestJsonApi(string $method, string $url, array|string|null $body = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => self::MEDIA, 'HTTP_ACCEPT' => self::MEDIA];
        foreach ($headers as $name => $value) {
            $key = strtoupper(str_replace('-', '_', $name));
            $key = $key === 'CONTENT_TYPE' ? $key : 'HTTP_'.$key;
            if ($value === null) {
                unset($server[$key]);
            } else {
                $server[$key] = $value;
            }
        }
        $content = is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body;
        $this->client->request($method, $url, server: $server, content: $content);

        $response = $this->client->getResponse();
        $this->recordHttpResponse($method, $url, $response->getStatusCode());

        return $response;
    }

    protected function recordHttpResponse(string $method, string $url, int $status): void
    {
        $this->httpTrace[] = ['method' => $method, 'url' => $url, 'status' => $status];
    }

    /** @return array<string, mixed> */
    protected function decodeJsonApi(Response $response, int $status = 200): array
    {
        self::assertSame($status, $response->getStatusCode(), substr((string) $response->getContent(), 0, 3000));
        self::assertStringStartsWith(self::MEDIA, (string) $response->headers->get('Content-Type'));
        $document = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        self::assertFalse(isset($document['data'], $document['errors']));

        return $document;
    }

    protected function assertJsonApiDocument(array $document): void
    {
        self::assertArrayHasKey('data', $document);
        self::assertSame('1.1', $document['jsonapi']['version']);
        self::assertArrayHasKey('self', $document['links']);
    }

    protected function assertResourceIdentifier(array $resource, string $type, ?string $id = null): void
    {
        self::assertSame($type, $resource['type']);
        self::assertIsString($resource['id']);
        if ($id !== null) {
            self::assertSame($id, $resource['id']);
        }
    }

    protected function assertResourceObject(array $resource, string $type, ?string $id = null): void
    {
        $this->assertResourceIdentifier($resource, $type, $id);
        self::assertArrayHasKey('self', $resource['links']);
        self::assertStringContainsString('/'.$type.'/'.$resource['id'], $resource['links']['self']);
    }

    /** @return array<string, mixed> */
    protected function assertJsonApiError(Response $response, int $status, ?string $pointer = null, ?string $parameter = null, ?string $header = null): array
    {
        $document = $this->decodeJsonApi($response, $status);
        self::assertArrayNotHasKey('data', $document);
        self::assertNotEmpty($document['errors']);
        foreach ($document['errors'] as $error) {
            self::assertSame((string) $status, $error['status']);
            self::assertNotEmpty($error['code'] ?? null);
            self::assertNotEmpty($error['title'] ?? null);
        }
        if ($pointer !== null) {
            self::assertContains($pointer, array_column(array_column($document['errors'], 'source'), 'pointer'));
        }
        if ($parameter !== null) {
            self::assertContains($parameter, array_column(array_column($document['errors'], 'source'), 'parameter'));
        }
        if ($header !== null) {
            self::assertContains($header, array_column(array_column($document['errors'], 'source'), 'header'));
        }

        return $document;
    }

    protected function identifier(string $key, string $type): array
    {
        return ['type' => $type, 'id' => $this->ids[$key]];
    }

    protected function articlePayload(array $attributes = [], array $relationships = []): array
    {
        return ['data' => ['type' => 'articles', 'attributes' => $attributes + ['title' => 'New article', 'slug' => 'new-article'],
            'relationships' => $relationships + ['author' => ['data' => $this->identifier('ada', 'authors')]]]];
    }

    protected function patchPayload(array|\stdClass $attributes, string $key = 'article-1'): array
    {
        return ['data' => ['type' => 'articles', 'id' => $this->ids[$key], 'attributes' => $attributes]];
    }

    protected function atomic(array $operations, array $headers = []): Response
    {
        return $this->requestJsonApi('POST', '/api/operations', ['atomic:operations' => $operations], $headers + ['Content-Type' => self::ATOMIC, 'Accept' => self::ATOMIC]);
    }

    protected function assertLinkage(array $actual, array $expected): void
    {
        $keys = static fn (array $rows): array => array_map(static fn (array $row): string => $row['type'].':'.$row['id'], $rows);
        self::assertEqualsCanonicalizing($keys($expected), $keys($actual));
        self::assertCount(count(array_unique($keys($actual))), $actual, 'Linkage must not contain duplicates.');
    }

    protected function collection(array $query = [], string $type = 'articles'): array
    {
        return $this->decodeJsonApi($this->requestJsonApi('GET', '/api/'.$type.'?'.http_build_query($query)));
    }
    protected function tearDown(): void
    {
        if (getenv('ACCEPTANCE_RECORD_HTTP') === '1') {
            $directory = dirname(__DIR__, 3).'/var';
            if (!is_dir($directory)) { mkdir($directory, 0775, true); }
            file_put_contents($directory.'/acceptance-http.ndjson', json_encode([
                'test' => static::class.'::'.$this->nameWithDataSet(),
                'requests' => $this->httpTrace,
            ], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
        }
        parent::tearDown();
    }
}
