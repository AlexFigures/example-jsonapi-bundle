<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Atomic;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConfigurationMatrixTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_atomic_'.$this->dataName(); }

    #[DataProvider('returnPolicies')]
    public function testReturnPolicyControlsRepresentation(int $status): void
    {
        $response = $this->atomic([['op' => 'update', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']], 'data' => $this->patchPayload(['title' => 'Atomic matrix'])['data']]]);
        self::assertSame($status, $response->getStatusCode(), (string) $response->getContent());
        if ($status === 204) { self::assertSame('', $response->getContent()); }
    }

    public static function returnPolicies(): iterable
    {
        yield 'none' => [204];
        yield 'auto' => [200];
        yield 'always' => [200];
    }

    #[DataProvider('restrictions')]
    public function testAtomicConfigurationGuards(string $case): void
    {
        if ($case === 'max') {
            $operation = ['op' => 'update', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']], 'data' => $this->patchPayload(['title' => 'Guard update'])['data']];
            $this->assertJsonApiError($this->atomic([$operation, $operation]), 400);
        } elseif ($case === 'href') {
            $this->assertJsonApiError($this->atomic([['op' => 'remove', 'href' => $this->url()]]), 400);
        } elseif ($case === 'lid') {
            $this->assertJsonApiError($this->atomic([['op' => 'add', 'data' => ['type' => 'authors', 'lid' => 'local-author', 'attributes' => ['name' => 'Local author']]]]), 400);
        } else {
            $response = $this->requestJsonApi('POST', '/api/operations', ['atomic:operations' => [['op' => 'remove', 'ref' => ['type' => 'articles', 'id' => $this->ids['article-1']]]]]);
            self::assertContains($response->getStatusCode(), [200, 204], (string) $response->getContent());
        }
    }

    public static function restrictions(): iterable
    {
        foreach (['max', 'href', 'lid', 'noext'] as $case) { yield $case => [$case]; }
    }
}
