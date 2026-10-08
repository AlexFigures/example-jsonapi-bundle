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
        // Cookbook controllers prove optional metadata in disposable test environments.
        if (!in_array($this->environment, ['dev', 'prod'], true)) {
            $routes->import($this->getProjectDir().'/src/Controller/', 'attribute');
        }
        // Atomic routing is explicitly application-owned in this bundle integration.
        if ($this->getContainer()->getParameter('jsonapi.atomic.enabled')) {
            $routes->add('jsonapi_atomic', $this->getContainer()->getParameter('jsonapi.atomic.endpoint'))
                ->controller(\AlexFigures\JsonApi\Bridge\Symfony\Controller\AtomicController::class)
                ->methods(['POST']);
        }
    }
}
