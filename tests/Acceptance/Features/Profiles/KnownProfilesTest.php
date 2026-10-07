<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Profiles;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class KnownProfilesTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_known_profiles'; }
    public function testStrictUnknownProfilePolicyRejectsUnknownProfile(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url(), headers: ['Accept' => self::MEDIA.';profile="urn:example:profile:missing"']), 406);
    }
    public function testKnownProfilesComposeAndAreEchoed(): void
    {
        $profiles = 'urn:example:profile:cookbook urn:jsonapi:profile:rel-counts';
        $response = $this->requestJsonApi('GET', $this->url(), headers: ['Accept' => self::MEDIA.';profile="'.$profiles.'"']);
        $doc = $this->decodeJsonApi($response);
        self::assertTrue($doc['meta']['cookbook_profile']);
        self::assertSame(2, $doc['data']['relationships']['tags']['meta']['count']);
        foreach (explode(' ', $profiles) as $profile) { self::assertStringContainsString($profile, (string) $response->headers->get('Content-Type')); }
        self::assertContains('Accept', $response->getVary());
        self::assertNotEmpty($response->headers->get('Link'));
        self::assertStringContainsString('rel="profile"', (string) $response->headers->get('Link'));
    }
}
