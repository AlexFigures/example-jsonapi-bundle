<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\PgEntity\FeatureMemo;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class SharedValidationGroupsTest extends AcceptanceTestCase
{
    private string $memoId;
    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureMemo::class);
        $memo = new FeatureMemo('Valid memo'); $em->persist($memo); $em->flush(); $this->memoId = (string) $memo->id;
    }
    #[DataProvider('invalidWrites')]
    public function testDefaultAndConfiguredGroupsProduceAttributePointers(string $method, string $title): void
    {
        $data = ['type' => 'feature-memos', 'attributes' => ['title' => $title]];
        if ($method === 'PATCH') { $data['id'] = $this->memoId; }
        $response = $this->requestJsonApi($method, '/api/feature-memos'.($method === 'PATCH' ? '/'.$this->memoId : ''), ['data' => $data]);
        $doc = $this->assertJsonApiError($response, 422);
        self::assertContains('/data/attributes/title', array_column(array_column($doc['errors'], 'source'), 'pointer'));
    }
    public static function invalidWrites(): iterable
    {
        foreach (['POST', 'PATCH'] as $method) {
            yield $method.' Default' => [$method, ''];
            yield $method.' memo:write' => [$method, 'Four'];
        }
    }
    #[DataProvider('operationGroups')]
    public function testOperationSpecificGroupsRemainDistinct(string $method, string $title, int $status): void
    {
        $data = ['type' => 'feature-memos', 'attributes' => ['title' => $title]];
        if ($method === 'PATCH') { $data['id'] = $this->memoId; }
        $response = $this->requestJsonApi($method, '/api/feature-memos'.($method === 'PATCH' ? '/'.$this->memoId : ''), ['data' => $data]);
        if ($status === 422) { $this->assertJsonApiError($response, 422, '/data/attributes/title'); }
        else { self::assertSame($title, $this->decodeJsonApi($response, $status)['data']['attributes']['title']); }
    }
    public static function operationGroups(): iterable
    {
        yield 'create-specific rejection' => ['POST', 'Seven77', 422];
        yield 'create rule not applied on update' => ['PATCH', 'Seven77', 200];
        yield 'update-specific rejection' => ['PATCH', str_repeat('x', 61), 422];
        yield 'update rule not applied on create' => ['POST', str_repeat('x', 61), 201];
    }

}
