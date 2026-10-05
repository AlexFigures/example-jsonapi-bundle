<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RepresentationModesTest extends AcceptanceTestCase
{
    protected function environment(): string
    {
        return 'features';
    }

    public function testNeverOmitsUnrequestedLinkage(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url()));
        self::assertArrayNotHasKey('data', $doc['data']['relationships']['author']);
    }

    public function testExplicitIncludeRetainsConnectedCompoundDocument(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author'));
        self::assertSame($this->ids['ada'], $doc['data']['relationships']['author']['data']['id']);
        self::assertSame($this->ids['ada'], $doc['included'][0]['id']);
    }

    public function testPrimaryIdentitiesDoNotReappearInIncluded(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author.articles'));
        foreach ($doc['included'] as $resource) {
            self::assertNotSame('articles:'.$this->ids['article-1'], $resource['type'].':'.$resource['id']);
        }
    }

    #[DataProvider('toManyWrites')]
    public function testToManyWrite204Modes(string $method): void
    {
        $response = $this->requestJsonApi($method, $this->url().'/relationships/tags', ['data' => [$this->identifier('php', 'tags')]]);
        self::assertSame(204, $response->getStatusCode(), (string) $response->getContent());
        self::assertSame('', $response->getContent());
    }

    public static function toManyWrites(): iterable
    {
        foreach (['PATCH', 'POST', 'DELETE'] as $method) { yield $method => [$method]; }
    }

    public function testRelationshipWrite204HasNoDocument(): void
    {
        $response = $this->requestJsonApi('PATCH', $this->url().'/relationships/editor', ['data' => $this->identifier('ada', 'authors')]);
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'/relationships/editor'));
        self::assertSame($this->ids['ada'], $doc['data']['id']);
    }
}
