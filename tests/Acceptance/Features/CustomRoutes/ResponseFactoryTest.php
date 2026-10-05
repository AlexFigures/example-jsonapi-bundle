<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\CustomRoutes;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ResponseFactoryTest extends AcceptanceTestCase
{
    #[DataProvider('forms')]
    public function testFactoryForms(string $form, int $status): void
    {
        $response = $this->requestJsonApi('GET', '/cookbook/responses/'.$form);
        self::assertSame($status, $response->getStatusCode(), (string) $response->getContent());
        if ($status === 204) {
            self::assertSame('', $response->getContent());
            return;
        }
        $document = $this->decodeJsonApi($response, $status);
        if ($status >= 400) {
            self::assertArrayHasKey('errors', $document);
            return;
        }
        self::assertTrue($document['meta']['cookbook']);
        self::assertSame('yes', $response->headers->get('X-Cookbook'));
        $resource = $form === 'collection' ? $document['data'][0] : $document['data'];
        self::assertSame('Ada Lovelace', $resource['attributes']['name']);
    }

    public function testResponseBuilderModifiersCompose(): void
    {
        $response = $this->requestJsonApi('GET', '/cookbook/responses/modifiers');
        $doc = $this->decodeJsonApi($response, 203);
        self::assertSame('https://example.test/help', $doc['links']['help']);
        self::assertSame(['name' => 'Ada Lovelace'], $doc['data'][0]['attributes']);
        self::assertNotEmpty($doc['included']);
        foreach ($doc['included'] as $included) {
            self::assertSame(['title'], array_keys($included['attributes']));
        }
        self::assertSame(1, $doc['meta']['total']);
    }

    public static function forms(): iterable
    {
        foreach (['resource' => 200, 'created' => 201, 'collection' => 200, 'accepted' => 202, 'no-content' => 204, 'error' => 409, 'validation' => 422] as $form => $status) {
            yield $form => [$form, $status];
        }
    }
}
