<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\{ProductionTestCase, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

final class ExtensibilityTest extends ProductionTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('EXTENSIBILITY-PROFILE-DI')]
    public function testPublicProfileSupportsConstructorInjectedUserContext(): void
    {
        self::ensureKernelShutdown();
        $failure = null;
        try {
            $this->client = static::createClient(['environment' => 'publishing_di', 'debug' => false]);
            $response = $this->asUser('editor-a', 'GET', '/api/articles');
        } catch (\Throwable $error) {
            $failure = $error->getMessage();
        }
        self::assertNull($failure, 'A public profile with an injected application context must boot: '.$failure);
        $this->decodeJsonApi($response);
    }
}
