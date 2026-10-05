<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ResourceDiscoveryTest extends KernelTestCase
{
    #[DataProvider('invalidResources')]
    public function testInvalidResourcesFailDuringBootOrRouteDiscoveryWithUsefulDiagnostic(string $environment, string $diagnostic): void
    {
        $failure = null;
        try {
            self::bootKernel(['environment' => $environment, 'debug' => false]);
            self::getContainer()->get('router')->getRouteCollection();
        } catch (\Throwable $error) { $failure = $error->getMessage(); }
        self::assertNotNull($failure, 'Invalid metadata must fail before serving HTTP requests.');
        self::assertStringContainsString($diagnostic, $failure);
    }
    public static function invalidResources(): iterable
    {
        yield 'duplicate type' => ['features_discovery_duplicate', 'Duplicate resource type'];
        yield 'duplicate attribute' => ['features_discovery_invalid', 'Duplicate attribute'];
    }
}
