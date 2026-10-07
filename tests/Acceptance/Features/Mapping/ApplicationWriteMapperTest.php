<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class ApplicationWriteMapperTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_write_mapper'; }

    public function testApplicationMapperAndDependencyInjectionComposeWithGeneratedWrites(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-articles', ['data' => ['type' => 'feature-articles', 'attributes' => ['title' => 'A sufficiently long application input']]], ['Authorization' => 'Bearer admin']), 201)['data'];
        self::assertSame('Edited: A sufficiently long application input', $created['attributes']['title']);
        $url = '/api/feature-articles/'.$created['id'];
        $payload = ['data' => ['type' => 'feature-articles', 'id' => $created['id'], 'attributes' => ['title' => 'Updated title']]];
        self::assertSame('Edited: Updated title', $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, $payload, ['Authorization' => 'Bearer admin']))['data']['attributes']['title']);
        $payload['data']['attributes']['title'] = 'Tiny';
        $this->assertJsonApiError($this->requestJsonApi('PATCH', $url, $payload, ['Authorization' => 'Bearer admin']), 422, '/data/attributes/title');
        self::assertSame('Edited: Updated title', $this->decodeJsonApi($this->requestJsonApi('GET', $url, headers: ['Authorization' => 'Bearer admin']))['data']['attributes']['title']);
    }
}
