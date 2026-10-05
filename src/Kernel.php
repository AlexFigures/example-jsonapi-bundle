<?php

declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait { configureRoutes as private importApplicationRoutes; }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $this->importApplicationRoutes($routes);
        // Atomic routing is explicitly application-owned in this bundle integration.
        if ($this->getContainer()->getParameter('jsonapi.atomic.enabled')) {
            $routes->add('jsonapi_atomic', $this->getContainer()->getParameter('jsonapi.atomic.endpoint'))
                ->controller(\AlexFigures\Symfony\Bridge\Symfony\Controller\AtomicController::class)
                ->methods(['POST']);
        }
    }
}
