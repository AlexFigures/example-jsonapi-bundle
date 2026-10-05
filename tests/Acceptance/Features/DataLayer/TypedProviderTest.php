<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\DataLayer;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use App\Tests\Acceptance\Support\ExpectedBundleGap;

final class TypedProviderTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_typed'; }

    #[DataProvider('types')]
    public function testGeneratedReadsDispatchByResourceType(string $type, string $id, string $title): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/'.$type));
        self::assertSame($title, $doc['data'][0]['attributes']['title']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/'.$type.'/'.$id));
        self::assertSame($title, $doc['data']['attributes']['title']);
    }

    #[DataProvider('types')]
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DX-TYPED-PERSISTER-DISPATCH')]
    public function testDocumentedTypedPersisterRegistrationHandlesGeneratedWrites(string $type, string $id, string $title): void
    {
        $response = $this->requestJsonApi('POST', '/api/'.$type, ['data' => ['type' => $type, 'attributes' => ['title' => 'Command']]]);
        $doc = $this->decodeJsonApi($response, 201);
        self::assertSame('Typed: Command', $doc['data']['attributes']['title']);
    }

    public static function types(): iterable
    {
        yield 'cards' => ['memory-cards', 'card', 'Card provider'];
        yield 'notes' => ['memory-notes', 'note', 'Note provider'];
    }
}
