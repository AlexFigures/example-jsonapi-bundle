<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Resource;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ResourceDeleteTest extends AcceptanceTestCase
{
    public function testDeleteAndSubsequentNotFound(): void
    {
        $response = $this->requestJsonApi('DELETE', $this->url());
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url()), 404);
    }

    public function testUnknownResource(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('DELETE', '/api/articles/999999'), 404);
    }

    public function testCascadeRemovesOwnedArticles(): void
    {
        $response = $this->requestJsonApi('DELETE', $this->url('ada', 'authors'));
        self::assertSame(204, $response->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url('article-1')), 404);
    }

    #[ExpectedBundleGap('DOCTRINE-002')]
    public function testForeignKeyRestrictionIsAnErrorDocument(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('DELETE', $this->url('root', 'categories')), 422);
        $this->decodeJsonApi($this->requestJsonApi('GET', $this->url('root', 'categories')));
    }
}
