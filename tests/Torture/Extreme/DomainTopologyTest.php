<?php

declare(strict_types=1);

namespace App\Tests\Torture\Extreme;

use App\Tests\Torture\Support\TortureTestCase;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

#[Group('extreme')]
final class DomainTopologyTest extends TortureTestCase
{
    public function testAssociationEntityHasStateAndNestedUserInclude(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/projects/p-1?include=memberships.user&fields[project-memberships]=role,user,joinedAt,notificationSettings'));
        $memberships = array_values(array_filter($doc['included'], static fn (array $r): bool => $r['type'] === 'project-memberships'));
        self::assertCount(5, $memberships);
        foreach ($memberships as $member) {
            self::assertSame('reader', $member['attributes']['role']);
            self::assertSame(['email' => true], $member['attributes']['notificationSettings']);
            self::assertStringStartsWith('2026-01-01', $member['attributes']['joinedAt']);
            self::assertSame('workspace-users', $member['relationships']['user']['data']['type']);
        }
        self::assertCount(count($doc['included']), array_unique(array_map(static fn (array $r): string => $r['type'].':'.$r['id'], $doc['included'])));
    }

    public function testMembershipFilterSortAndTraversal(): void
    {
        $memberships = $this->collection(['filter' => ['role' => 'editor'], 'sort' => 'role,id', 'page' => ['size' => 20]], 'project-memberships');
        self::assertCount(5, $memberships['data']);
        $projects = $this->collection(['filter' => ['memberships.role' => 'editor'], 'sort' => 'memberships.joinedAt,id'], 'projects');
        self::assertSame(['p-2'], array_column($projects['data'], 'id'));
        $user = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/project-memberships/m-1/user'));
        $this->assertResourceIdentifier($user['data'], 'workspace-users', 'u-1');
    }

    public function testSelfGraphTerminatesAndDeduplicates(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/2?include=parent.children.parent'));
        $keys = array_map(static fn (array $r): string => $r['type'].':'.$r['id'], $doc['included']);
        self::assertContains('tasks:1', $keys);
        self::assertSame(count($keys), count(array_unique($keys)));
        self::assertLessThanOrEqual(5, count($keys));
        foreach ($doc['included'] as $resource) { self::assertIsString($resource['id']); }
    }

    public function testIncludeDepthRejectedBeforeSql(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/tasks/2?include=parent.children.parent.children.parent.children'), 400);
        self::assertSame(0, $this->lastMetric()['query_count']);
    }

    public function testNaturalIdentifierCrudQueryAndRelationship(): void
    {
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/countries', ['data' => ['type' => 'countries', 'id' => 'AM', 'attributes' => ['name' => 'Armenia']]]), 201);
        $this->assertResourceIdentifier($created['data'], 'countries', 'AM');
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/countries/AM', ['data' => ['type' => 'countries', 'id' => 'AM', 'attributes' => ['name' => 'Armenia updated']]]));
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/workspace-users/u-1/relationships/country', ['data' => ['type' => 'countries', 'id' => 'AM']]));
        $this->assertResourceIdentifier($this->decodeJsonApi($this->requestJsonApi('GET', '/api/workspace-users/u-1/country'))['data'], 'countries', 'AM');
        self::assertCount(1, $this->collection(['filter' => ['name' => 'Armenia updated'], 'sort' => 'name'], 'countries')['data']);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', '/api/workspace-users/u-1/relationships/country', ['data' => null]));
        self::assertSame(204, $this->requestJsonApi('DELETE', '/api/countries/AM')->getStatusCode());
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/countries/AM'), 404);
    }

    public function testBigintIdentifierIsLossless(): void
    {
        $id = '9223372036854775806';
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->taskUrl($id)));
        $this->assertResourceObject($doc['data'], 'tasks', $id);
        $this->decodeJsonApi($this->requestJsonApi('PATCH', $this->taskUrl($id), $this->taskPatch('BIGINT updated', $id)));
        self::assertSame($id, $this->decodeJsonApi($this->requestJsonApi('GET', $this->taskUrl($id)))['data']['id']);
    }

    public function testInheritanceIdentityAndIncludedSubclasses(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/tasks/1?include=attachments'));
        $assets = array_values(array_filter($doc['included'], static fn (array $r): bool => $r['type'] === 'assets'));
        $this->assertLinkage($assets, [['type' => 'assets', 'id' => 'document-1'], ['type' => 'assets', 'id' => 'video-1']]);
        self::assertSame('Document', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/assets/document-1'))['data']['attributes']['name']);
        self::assertSame('Video', $this->decodeJsonApi($this->requestJsonApi('GET', '/api/assets/video-1'))['data']['attributes']['name']);
    }
}
