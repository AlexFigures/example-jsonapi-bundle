<?php

declare(strict_types=1);

namespace App\Tests\Torture\Support;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Torture\Infrastructure\TortureFixtures;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{Request, Response};

abstract class TortureTestCase extends AcceptanceTestCase
{
    protected string $dataset = 'small';
    protected ?int $rows = null;
    protected array $metrics = [];

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'torture', 'debug' => false]);
        $registry = self::getContainer()->get(ManagerRegistry::class);
        $this->ids = (new TortureFixtures($registry))->reset($this->dataset, $this->rows);
        $this->metrics = [];
    }

    protected function requestJsonApi(string $method, string $url, array|string|null $body = null, array $headers = []): Response
    {
        $response = parent::requestJsonApi($method, $url, $body, $headers);
        $this->record($response);
        return $response;
    }

    protected function record(Response $response): void
    {
        self::assertTrue($response->headers->has('X-Torture-Metrics'), 'The SQL/memory instrumentation must be active.');
        $metric = json_decode($response->headers->get('X-Torture-Metrics'), true, 512, JSON_THROW_ON_ERROR);
        $this->metrics[] = $metric;
        file_put_contents(dirname(__DIR__, 3).'/var/torture/scenarios.ndjson', json_encode([
            'test' => static::class.'::'.$this->nameWithDataSet(), 'metric' => $metric,
        ], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
    }

    protected function lastMetric(): array { return $this->metrics[array_key_last($this->metrics)]; }

    protected function concurrent(array $requests): array
    {
        $results = ConcurrentRequests::run($requests);
        foreach ($results as $result) {
            self::assertIsArray($result['metric']);
            $this->metrics[] = $result['metric'];
            file_put_contents(dirname(__DIR__, 3).'/var/torture/scenarios.ndjson', json_encode([
                'test' => static::class.'::'.$this->nameWithDataSet(), 'metric' => $result['metric'],
            ], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
        }
        return $results;
    }

    /** Model a persistent worker; the optional reset is the Symfony host lifecycle. */
    protected function workerRequest(string $method, string $url, array $headers = [], ?array $body = null, bool $resetAfter = false): Response
    {
        $server = ['CONTENT_TYPE' => self::MEDIA, 'HTTP_ACCEPT' => self::MEDIA];
        foreach ($headers as $name => $value) {
            $key = strtoupper(str_replace('-', '_', $name));
            $server[$key === 'CONTENT_TYPE' ? $key : 'HTTP_'.$key] = $value;
        }
        $request = Request::create($url, $method, server: $server, content: $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR));
        $response = $this->client->getKernel()->handle($request);
        $this->client->getKernel()->terminate($request, $response);
        $this->record($response);
        if ($resetAfter) { $this->client->getKernel()->getContainer()->get('services_resetter')->reset(); }
        return $response;
    }

    protected function taskUrl(int|string $id = 1): string { return '/api/tasks/'.$id; }
    protected function taskPatch(string $title, int|string $id = 1): array { return ['data' => ['type' => 'tasks', 'id' => (string) $id, 'attributes' => ['title' => $title]]]; }
}
