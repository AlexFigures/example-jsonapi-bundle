<?php

declare(strict_types=1);

namespace App\Tests\Torture\Chaos;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('chaos')]
final class HttpBoundaryTest extends TortureTestCase
{
    public static function malformedBodies(): iterable
    {
        yield 'invalid UTF-8' => ['{"data":{"type":"tasks","attributes":{"title":"'."\xFF".'"}}}'];
        yield 'excessive nesting' => [str_repeat('[', 600).'0'.str_repeat(']', 600)];
        yield 'huge integer identifier' => ['{"data":{"type":"tasks","id":922337203685477580612345,"attributes":{"title":"Overflow"}}}'];
    }

    #[DataProvider('malformedBodies')]
    public function testMalformedInputFailsAtHttpBoundary(string $body): void
    {
        $response = $this->requestJsonApi('PATCH', '/api/tasks/1', $body);
        self::assertGreaterThanOrEqual(400, $response->getStatusCode());
        self::assertLessThan(500, $response->getStatusCode());
        $this->assertJsonApiError($response, $response->getStatusCode());
        self::assertSame('Same', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/1'))['data']['attributes']['title']);
    }

    public function testLargeValidTextAndUnicodeRoundTripWithoutIngressPolicy(): void
    {
        $text = str_repeat('Текст 🧪 ', 180000);
        $document = $this->decodeJsonApi($this->workerRequest('PATCH', '/api/tasks/1', body: ['data' => [
            'type' => 'tasks', 'id' => '1', 'attributes' => ['description' => $text],
        ]]));
        self::assertSame(hash('sha256', $text), hash('sha256', $document['data']['attributes']['description']));
        self::assertGreaterThan(2000000, $this->lastMetric()['response_bytes']);
    }

    public function testManyUnknownMembersHaveControlledError(): void
    {
        $attributes = array_fill_keys(array_map(static fn (int $i): string => 'unknown-'.$i, range(1, 1000)), 'value');
        $attributes[str_repeat('x', 10000)] = 'long member';
        $response = $this->requestJsonApi('PATCH', '/api/tasks/1', ['data' => ['type' => 'tasks', 'id' => '1', 'attributes' => $attributes]]);
        self::assertGreaterThanOrEqual(400, $response->getStatusCode());
        self::assertLessThan(500, $response->getStatusCode());
        $this->assertJsonApiError($response, $response->getStatusCode());
    }

    #[Group('application-policy')]
    public function testOptInIngressLimitsRejectBeforeSql(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', '/api/tasks/1', ['data' => [
            'type' => 'tasks', 'id' => '1', 'attributes' => ['description' => str_repeat('x', 1100000)],
        ]], ['X-Torture-Policy' => 'ingress']), 413);
        self::assertSame(0, $this->lastMetric()['query_count']);
        $this->assertJsonApiError($this->requestJsonApi('PATCH', '/api/tasks/1/relationships/labels', ['data' => array_fill(0, 2001, [
            'type' => 'tags', 'id' => '1001',
        ])], ['X-Torture-Policy' => 'ingress']), 413);
        self::assertSame(0, $this->lastMetric()['query_count']);
    }

    public function testAtomicOperationLimitRejectsBeforeMutation(): void
    {
        $operation = ['op' => 'update', 'ref' => ['type' => 'projects', 'id' => 'p-1'], 'data' => ['type' => 'projects', 'attributes' => ['name' => 'Within limit']]];
        $results = $this->decodeJsonApi($this->atomic(array_fill(0, 20, $operation)));
        self::assertCount(20, $results['atomic:results']);
        $operation['data']['attributes']['name'] = 'Must not execute';
        $this->assertJsonApiError($this->atomic(array_fill(0, 21, $operation)), 400);
        self::assertSame(0, $this->lastMetric()['query_count']);
        self::assertSame('Within limit', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1'))['data']['attributes']['name']);
    }
}
