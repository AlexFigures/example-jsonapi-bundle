<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Configuration;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\Attributes\DataProvider;

final class PublicConfigurationTest extends KernelTestCase
{
    #[Group('bundle-gap')]
    #[ExpectedBundleGap('DX-PROFILE-COMMAND')]
    public function testProfileValidationCommandRunsFromConsumerContainer(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);
        $application = new Application($kernel);
        self::assertArrayHasKey('jsonapi:validate-profiles', $application->all(), 'Documented profile validation command must be registered.');
        $tester = new CommandTester($application->find('jsonapi:validate-profiles'));
        self::assertSame(0, $tester->execute([]), $tester->getDisplay());
        self::assertStringContainsString('valid', strtolower($tester->getDisplay()));
    }

    #[DataProvider('invalidEnvironments')]
    public function testUnsafeConfigurationFailsDuringBoot(string $environment, string $diagnostic): void
    {
        try {
            self::bootKernel(['environment' => $environment, 'debug' => false]);
            self::fail('Invalid public configuration must fail during boot.');
        } catch (\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException $error) {
            self::assertStringContainsString($diagnostic, $error->getMessage());
        }
    }

    public static function invalidEnvironments(): iterable
    {
        yield 'negative limit' => ['features_invalid_limit', 'filter_max_nodes'];
        yield 'sort policy' => ['features_invalid_sort', 'collection_sort_policy'];
        yield 'docs theme' => ['features_invalid_theme', 'theme'];
        yield 'provider' => ['features_invalid_provider', 'provider'];
        yield 'linkage mode' => ['features_invalid_linkage', 'linkage_in_resource'];
    }
}
