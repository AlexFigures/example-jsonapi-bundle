<?php

declare(strict_types=1);

namespace App\Controller;

use AlexFigures\Symfony\Bridge\Symfony\Routing\Attribute\MediaChannel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MediaChannelController
{
    #[Route('/cookbook/channel/path', name: 'cookbook.channel.path', methods: ['POST'])]
    public function path(): JsonResponse { return new JsonResponse(['channel' => 'path']); }

    #[Route('/cookbook/channel/route', name: 'cookbook.channel.route', methods: ['POST'])]
    public function route(): JsonResponse { return new JsonResponse(['channel' => 'route']); }

    #[Route('/cookbook/channel/attribute', name: 'cookbook.channel.attribute', methods: ['POST'])]
    #[MediaChannel('cookbook-attribute')]
    public function attribute(): JsonResponse { return new JsonResponse(['channel' => 'attribute']); }
}
