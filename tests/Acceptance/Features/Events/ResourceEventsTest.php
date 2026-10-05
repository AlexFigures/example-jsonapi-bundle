<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Events;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ResourceEventsTest extends AcceptanceTestCase
{
    #[DataProvider('mutations')]
    public function testGeneratedCrudPublishesResourceEvent(string $method, string $operation): void
    {
        $body = match ($method) { 'POST' => $this->articlePayload(), 'PATCH' => $this->patchPayload(['title' => 'Event update']), default => null };
        $response = $this->requestJsonApi($method, $method === 'POST' ? '/api/articles' : $this->url(), $body);
        self::assertContains($response->getStatusCode(), [200, 201, 204], (string) $response->getContent());
        $events = json_decode((string) $response->headers->get('X-Cookbook-Events'), true, 512, JSON_THROW_ON_ERROR);
        self::assertContains($operation, array_column($events, 'operation'));
        self::assertContains('resource', array_column($events, 'kind'));
        foreach ($events as $event) { self::assertFalse($event['transaction_active'], 'Resource notifications must describe the committed mutation.'); }
    }

    public static function mutations(): iterable
    {
        yield 'create' => ['POST', 'create'];
        yield 'update' => ['PATCH', 'update'];
        yield 'delete' => ['DELETE', 'delete'];
    }

    #[DataProvider('relationships')]
    public function testRelationshipEvents(string $method, string $operation): void
    {
        $response = $this->requestJsonApi($method, $this->url().'/relationships/tags', ['data' => [$this->identifier('api', 'tags')]]);
        self::assertSame(200, $response->getStatusCode());
        $events = json_decode((string) $response->headers->get('X-Cookbook-Events'), true, 512, JSON_THROW_ON_ERROR);
        self::assertContains($operation, array_column($events, 'operation'));
        self::assertContains('relationship', array_column($events, 'kind'));
        foreach ($events as $event) { self::assertFalse($event['transaction_active'], 'Relationship notifications must describe the committed mutation.'); }
    }

    public static function relationships(): iterable
    {
        yield 'replace' => ['PATCH', 'replace'];
        yield 'add' => ['POST', 'add'];
        yield 'remove' => ['DELETE', 'remove'];
    }
    public function testRejectedMutationProducesNoResourceNotification(): void
    {
        $response = $this->requestJsonApi('PATCH', $this->url(), $this->patchPayload(['title' => '']));
        $this->assertJsonApiError($response, 422);
        $events = json_decode((string) $response->headers->get('X-Cookbook-Events', '[]'), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([], $events);
    }

}
