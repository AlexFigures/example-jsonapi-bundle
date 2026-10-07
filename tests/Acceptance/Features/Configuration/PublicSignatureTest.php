<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Configuration;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Consumer compilation/API audit complements HTTP behavior, without testing controllers. */
final class PublicSignatureTest extends TestCase
{
    #[DataProvider('signatures')]
    public function testPublicExtensionSignatureUsesSupportedDto(string $interface, string $method, ?int $parameter): void
    {
        $signature = new \ReflectionMethod($interface, $method);
        $type = $parameter === null ? $signature->getReturnType() : $signature->getParameters()[$parameter]->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        $dto = $type->getName();
        self::assertStringNotContainsString('@internal', (new \ReflectionClass($dto))->getDocComment() ?: '', 'Public extension signatures require a supported DTO contract or a public replacement.');
    }
    public static function signatures(): iterable
    {
        yield 'batch reader return' => [\AlexFigures\Symfony\Contract\Data\RelationshipBatchReaderInterface::class, 'read', null];
        yield 'preloader return' => [\AlexFigures\Symfony\Contract\Data\RepresentationPreloaderInterface::class, 'preload', null];
        yield 'custom route registry addRoute' => [\AlexFigures\Symfony\Resource\Registry\CustomRouteRegistryInterface::class, 'addRoute', 0];
    }

    public function testDecoratorQueryPlanCapabilityIsPublic(): void
    {
        foreach ([
            new \ReflectionClass(\AlexFigures\Symfony\Bridge\Doctrine\Query\DoctrineCollectionQueryProviderInterface::class),
            new \ReflectionClass(\AlexFigures\Symfony\Bridge\Symfony\Locator\ResourceRepositoryLocator::class),
            new \ReflectionMethod(\AlexFigures\Symfony\Bridge\Symfony\Locator\ResourceRepositoryLocator::class, 'getRepositoryForType'),
        ] as $contract) {
            $documentation = $contract->getDocComment() ?: '';
            self::assertStringContainsString('@api', $documentation);
            self::assertStringNotContainsString('@internal', $documentation);
        }
    }
}
