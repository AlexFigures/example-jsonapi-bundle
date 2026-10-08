<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\DataLayer;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TypedRelationshipTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_typed_relationships'; }

    public static function types(): iterable
    {
        yield 'autoconfigured cards' => ['memory-cards', 'card', 'Card relationship adapter'];
        yield 'explicitly tagged notes' => ['memory-notes', 'note', 'Note relationship adapter'];
    }

    #[DataProvider('types')]
    public function testAllEndpointOperationsDispatchBySourceType(string $type, string $id, string $title): void
    {
        $this->client->disableReboot();
        $base = '/api/'.$type.'/'.$id;
        $identifier = ['type' => $type, 'id' => $id];
        self::assertSame($identifier, $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/relationships/related'))['data']);
        self::assertSame([$identifier], $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/relationships/peers'))['data']);
        self::assertSame($title, $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/related'))['data']['attributes']['title']);
        self::assertSame($title, $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/peers'))['data'][0]['attributes']['title']);
        self::assertSame([], $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/relationships/peers?page[number]=2&page[size]=1'))['data']);

        foreach ([null, $identifier] as $target) {
            $response = $this->requestJsonApi('PATCH', $base.'/relationships/related', ['data' => $target]);
            self::assertContains($response->getStatusCode(), [200, 204], (string) $response->getContent());
            self::assertSame($target, $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/relationships/related'))['data']);
        }
        foreach ([['PATCH', [], []], ['POST', [$identifier], [$identifier]], ['DELETE', [$identifier], []], ['PATCH', [$identifier], [$identifier]]] as [$method, $targets, $expected]) {
            $response = $this->requestJsonApi($method, $base.'/relationships/peers', ['data' => $targets]);
            self::assertContains($response->getStatusCode(), [200, 204], (string) $response->getContent());
            self::assertSame($expected, $this->decodeJsonApi($this->requestJsonApi('GET', $base.'/relationships/peers'))['data']);
        }
        $other = $type === 'memory-cards' ? 'memory-notes/note' : 'memory-cards/card';
        self::assertCount(1, $this->decodeJsonApi($this->requestJsonApi('GET', '/api/'.$other.'/relationships/peers'))['data']);
    }

    public function testUnmatchedTypeUsesConfiguredReaderFallback(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/memory-articles/one/relationships/related'));
        self::assertSame(['type' => 'memory-articles', 'id' => 'two'], $doc['data']);
    }
}
