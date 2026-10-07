<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\ProductionTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

final class ReadModelTest extends ProductionTestCase
{
    public function testAggregateResourceIsIndependentOfDoctrineWriteModel(): void
    {
        $doc = $this->decodeJsonApi($this->asUser('editor-a', 'GET', $this->url('ada', 'authors').'/publishing-statistics'));
        self::assertSame('author-publishing-statistics', $doc['data']['type']);
        self::assertSame($this->ids['ada'], $doc['data']['id']);
        self::assertSame(['draftCount' => 4, 'publishedCount' => 4], $doc['data']['attributes']);
        self::assertArrayNotHasKey('email', $doc['data']['attributes']);
    }

    public function testAggregateAuthorization(): void
    {
        $this->assertJsonApiError($this->asUser('editor-a', 'GET', $this->url('grace', 'authors').'/publishing-statistics'), 403);
    }

    public function testReadModelHasNoGeneratedWriteRoutes(): void
    {
        self::assertSame(404, $this->asUser('admin', 'POST', '/api/author-publishing-statistics', ['data' => ['type' => 'author-publishing-statistics']])->getStatusCode());
    }
    public function testUnknownAuthorHasControlledError(): void
    {
        $this->assertJsonApiError($this->asUser('admin', 'GET', '/api/authors/999999/publishing-statistics'), 404);
    }

}
