<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Doctrine;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;

final class UuidIdentifierTest extends AcceptanceTestCase
{
    public static function resources(): array
    {
        return [
            'UUID string' => ['subscriptions', 'subscription', 'email', 'updated@example.test'],
            'Doctrine UUID object' => ['newsletters', 'newsletter', 'subject', 'Updated edition'],
        ];
    }

    #[DataProvider('resources')]
    public function testServerGeneratedUuidCrud(string $type, string $key, string $field, string $value): void
    {
        $response = $this->requestJsonApi('POST', '/api/'.$type, ['data' => ['type' => $type, 'attributes' => [$field => $value]]]);
        $created = $this->decodeJsonApi($response, 201);
        $id = $created['data']['id'];
        self::assertIsString($id);
        self::assertTrue(Uuid::isValid($id));
        $url = '/api/'.$type.'/'.$id;
        self::assertStringEndsWith($url, $response->headers->get('Location'));
        $this->assertResourceObject($created['data'], $type, $id);
        $read = $this->decodeJsonApi($this->requestJsonApi('GET', $url));
        self::assertSame($value, $read['data']['attributes'][$field]);
        $changed = $field === 'email' ? 'changed@example.test' : 'Changed edition';
        $updated = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, ['data' => ['type' => $type, 'id' => $id, 'attributes' => [$field => $changed]]]));
        $this->assertResourceIdentifier($updated['data'], $type, $id);
        self::assertSame($changed, $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['attributes'][$field]);
        $deleted = $this->requestJsonApi('DELETE', $url);
        self::assertSame(204, $deleted->getStatusCode());
        self::assertSame('', $deleted->getContent());
        $this->assertJsonApiError($this->requestJsonApi('GET', $url), 404);
    }

    public function testClientAssignedStringUuid(): void
    {
        $id = 'a0372535-84e4-4d24-b5ad-e4c29b0ad099';
        $payload = ['data' => ['type' => 'subscriptions', 'id' => $id, 'attributes' => ['email' => 'client-uuid@example.test']]];
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/subscriptions', $payload), 201);
        $this->assertResourceIdentifier($created['data'], 'subscriptions', $id);
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', '/api/subscriptions/'.$id))['data'], 'subscriptions', $id);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/subscriptions', $payload), 409);
    }

    public function testClientAssignedDoctrineUuidObject(): void
    {
        $id = 'a0372535-84e4-4d24-b5ad-e4c29b0ad099';
        $payload = ['data' => ['type' => 'newsletters', 'id' => $id, 'attributes' => ['subject' => 'Client UUID edition']]];
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/newsletters', $payload), 201);
        $this->assertResourceIdentifier($created['data'], 'newsletters', $id);
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', '/api/newsletters/'.$id))['data'], 'newsletters', $id);
        $this->assertJsonApiError($this->requestJsonApi('POST', '/api/newsletters', $payload), 409);
    }

    public function testMalformedUuidDoesNotLeakServerError(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/newsletters/not-a-uuid'), 400);
    }

    public function testAtomicClientAssignedDoctrineUuidObject(): void
    {
        $id = 'a0372535-84e4-4d24-b5ad-e4c29b0ad099';
        $result = $this->decodeJsonApi($this->atomic([['op' => 'add', 'href' => '/api/newsletters', 'data' => [
            'type' => 'newsletters', 'id' => $id, 'attributes' => ['subject' => 'Client UUID atomic edition'],
        ]]]));
        $this->assertResourceIdentifier($result['atomic:results'][0]['data'], 'newsletters', $id);
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', '/api/newsletters/'.$id))['data'], 'newsletters', $id);
    }

    #[DataProvider('resources')]
    public function testMissingUuid(string $type, string $key, string $field, string $value): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/'.$type.'/00000000-0000-4000-8000-000000000001'), 404);
    }

    #[DataProvider('resources')]
    public function testRouteBodyUuidMismatch(string $type, string $key, string $field, string $value): void
    {
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $this->url($key, $type), ['data' => [
            'type' => $type, 'id' => '00000000-0000-4000-8000-000000000001', 'attributes' => [$field => $value],
        ]]), 409, '/data/id');
    }

    public function testUuidRelationshipsAndIncludedResources(): void
    {
        $url = $this->url('newsletter', 'newsletters');
        $previous = $this->identifier('previous-newsletter', 'newsletters');
        $recipient = $this->identifier('subscription', 'subscriptions');
        $document = $this->decodeJsonApi($this->requestJsonApi('GET', $url.'?include=previousEdition,recipient'));
        $this->assertResourceIdentifier($document['data'], 'newsletters', $this->ids['newsletter']);
        self::assertSame($previous, $document['data']['relationships']['previousEdition']['data']);
        self::assertSame($recipient, $document['data']['relationships']['recipient']['data']);
        $this->assertLinkage($document['included'], [$previous, $recipient]);
        $linkage = $this->decodeJsonApi($this->requestJsonApi('GET', $url.'/relationships/previousEdition'));
        self::assertSame($previous, $linkage['data']);
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', $url.'/previousEdition'))['data'], 'newsletters', $this->ids['previous-newsletter']);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $url.'/relationships/previousEdition', ['data' => null]));
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $url.'/relationships/previousEdition', ['data' => $previous]));
        self::assertSame($previous, $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['relationships']['previousEdition']['data']);
    }

    #[DataProvider('resources')]
    public function testAtomicUuidUpdateAndRemove(string $type, string $key, string $field, string $value): void
    {
        $updated = $this->decodeJsonApi($this->atomic([['op' => 'update', 'ref' => $this->identifier($key, $type),
            'data' => ['type' => $type, 'id' => $this->ids[$key], 'attributes' => [$field => $value]],
        ]]));
        $this->assertResourceIdentifier($updated['atomic:results'][0]['data'], $type, $this->ids[$key]);
        self::assertSame($value, $this->decodeJsonApi($this->requestJsonApi('GET', $this->url($key, $type)))['data']['attributes'][$field]);
        if ($type === 'subscriptions') {
            $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->url('newsletter', 'newsletters').'/relationships/recipient', ['data' => null]));
        }
        $this->decodeJsonApi($this->atomic([['op' => 'remove', 'ref' => $this->identifier($key, $type)]]));
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url($key, $type)), 404);
    }

    public function testAtomicUuidObjectCreationAndLocalId(): void
    {
        $result = $this->decodeJsonApi($this->atomic([
            ['op' => 'add', 'href' => '/api/newsletters', 'data' => ['type' => 'newsletters', 'lid' => 'new-edition', 'attributes' => ['subject' => 'Created atomically']]],
            ['op' => 'update', 'ref' => ['type' => 'newsletters', 'lid' => 'new-edition'], 'data' => ['type' => 'newsletters', 'lid' => 'new-edition', 'attributes' => ['subject' => 'Updated atomically']]],
        ]));
        self::assertCount(2, $result['atomic:results']);
        $id = $result['atomic:results'][0]['data']['id'];
        self::assertTrue(Uuid::isValid($id));
        self::assertSame($id, $result['atomic:results'][1]['data']['id']);
        self::assertSame('Updated atomically', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/newsletters/'.$id))['data']['attributes']['subject']);
    }
}
