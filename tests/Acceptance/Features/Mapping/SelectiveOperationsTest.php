<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class SelectiveOperationsTest extends AcceptanceTestCase
{
    public function testEnabledOperationsWorkAndDisabledOperationsAreAbsent(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-records', ['data' => ['type' => 'feature-records', 'attributes' => ['title' => 'Created']]]), 201)['data'];
        $url = '/api/feature-records/'.$created['id'];
        self::assertSame('Created', $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['attributes']['title']);
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url, ['data' => ['type' => 'feature-records', 'id' => $created['id'], 'attributes' => ['title' => 'Updated']]]));
        self::assertSame('Updated', $doc['data']['attributes']['title']);
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/feature-records'), 405);
        $this->assertJsonApiError($this->requestJsonApi('DELETE', $url), 405);
    }
    public function testOptionsReflectsDifferentCollectionAndItemOperations(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/feature-records', ['data' => ['type' => 'feature-records', 'attributes' => ['title' => 'Options fixture']]]), 201)['data'];
        foreach (['/api/feature-records' => ['POST', 'OPTIONS'], '/api/feature-records/'.$created['id'] => ['GET', 'HEAD', 'PATCH', 'OPTIONS']] as $url => $expected) {
            $response = $this->requestJsonApi('OPTIONS', $url);
            self::assertSame(204, $response->getStatusCode());
            self::assertEqualsCanonicalizing($expected, array_map('trim', explode(',', (string) $response->headers->get('Allow'))));
        }
    }
}
