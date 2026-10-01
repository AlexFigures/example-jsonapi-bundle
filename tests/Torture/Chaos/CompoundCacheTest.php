<?php

declare(strict_types=1);

namespace App\Tests\Torture\Chaos;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('chaos')]
final class CompoundCacheTest extends TortureTestCase
{
    public function testCompoundRepresentationTracksRelatedMutation(): void
    {
        $url = '/api/tasks/1?include=assignee,labels';
        $first = $this->requestJsonApi('GET', $url);
        $etag = $first->headers->get('ETag');
        self::assertNotEmpty($etag);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/workspace-users/u-1', ['data' => [
            'type' => 'workspace-users', 'id' => 'u-1', 'attributes' => ['name' => 'Changed assignee'],
        ]]));
        $changed = $this->requestJsonApi('GET', $url, headers: ['If-None-Match' => $etag]);
        $document = $this->decodeJsonApi($changed);
        self::assertContains('Changed assignee', array_column(array_column($document['included'], 'attributes'), 'name'));
        self::assertNotSame($etag, $changed->headers->get('ETag'));
        $etag = $changed->headers->get('ETag');
        $this->decodeJsonApi($this->requestJsonApi('DELETE', '/api/tasks/1/relationships/labels', ['data' => [['type' => 'tags', 'id' => '1001']]]));
        $this->decodeJsonApi($this->requestJsonApi('GET', $url, headers: ['If-None-Match' => $etag]));
    }

    public function testProfileRepresentationAndConditionalStateDoNotLeakInWorker(): void
    {
        $base = $this->workerRequest('GET', '/api/tasks/1');
        $etag = $base->headers->get('ETag');
        $profile = $this->workerRequest('GET', '/api/tasks/1', ['Accept' => self::MEDIA.';profile="urn:jsonapi:profile:rel-counts"', 'If-None-Match' => $etag]);
        $document = $this->decodeJsonApi($profile);
        self::assertSame(5, $document['data']['relationships']['labels']['meta']['count']);
        self::assertNotSame($etag, $profile->headers->get('ETag'));
        self::assertSame(304, $this->workerRequest('GET', '/api/tasks/1', ['If-None-Match' => $etag])->getStatusCode());
        self::assertSame(200, $this->workerRequest('GET', '/api/tasks/1')->getStatusCode());
    }
}
