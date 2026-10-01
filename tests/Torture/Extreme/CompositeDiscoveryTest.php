<?php

declare(strict_types=1);

namespace App\Tests\Torture\Extreme;

use App\Kernel;
use App\Tests\Torture\Support\ExpectedTortureGap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('extreme')]
final class CompositeDiscoveryTest extends TestCase
{
    #[Group('torture-gap')]
    #[ExpectedTortureGap('ARCHITECTURE-COMPOSITE-ID')]
    public function testUnsupportedCompositeIdIsRejectedDuringRouteDiscovery(): void
    {
        $kernel = new Kernel('torture_composite', false);
        $diagnostic = null;
        try {
            $kernel->boot();
            // Public Symfony route discovery is the application's configuration boundary.
            $kernel->getContainer()->get('router')->getRouteCollection();
        } catch (\Throwable $exception) {
            $diagnostic = $exception;
        } finally {
            $kernel->shutdown();
        }
        self::assertNotNull($diagnostic, 'Composite identifiers need an explicit discovery diagnostic; exposing one component as a resource ID is ambiguous.');
        self::assertMatchesRegularExpression('/composite|multiple.*identifier/i', $diagnostic->getMessage());
    }
}
