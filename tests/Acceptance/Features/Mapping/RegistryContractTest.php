<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Mapping;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;

final class RegistryContractTest extends AcceptanceTestCase
{
    public function testApplicationCanConsumeResourceAndCustomRouteRegistries(): void
    {
        $meta = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/resource-registry'))['meta'];
        self::assertTrue($meta['has_type']);
        self::assertSame('urn:example:profile:cookbook', $meta['profile_descriptor']['uri']);
        self::assertSame('Cookbook hooks', $meta['profile_descriptor']['name']);
        self::assertSame('1.0', $meta['profile_descriptor']['version']);
        self::assertSame('feature-memos', $meta['class_type']);
        self::assertContains('feature-articles', $meta['all_types']);
        self::assertContains('cookbook.options', $meta['route_names']);
        self::assertContains('cookbook.options', $meta['resource_route_names']);
        self::assertNotContains('articles.publish', $meta['resource_route_names']);
        self::assertSame('Read with route defaults and requirements', $meta['description']);
    }
    public function testResourceLevelRelationshipPolicyAppliesToUnspecifiedRelationship(): void
    {
        $meta = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/resource-registry'))['meta'];
        self::assertSame('verify', strtolower($meta['source_policy']));
    }    public function testProjectionDoesNotReplacePrimaryEntityClassRegistration(): void
    {
        $meta = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/resource-registry'))['meta'];
        self::assertSame('feature-articles', $meta['primary_class_type']);
    }
    public function testDocumentedDefaultMetadataImplementsPublicMetadataContract(): void
    {
        $meta = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/resource-registry'))['meta'];
        self::assertTrue($meta['metadata_contract'], 'ResourceMetadataInterface promises an attribute-based default implementation.');
    }

}
