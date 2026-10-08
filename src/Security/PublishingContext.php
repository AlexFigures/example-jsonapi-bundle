<?php

declare(strict_types=1);

namespace App\Security;

use AlexFigures\JsonApi\Http\Exception\JsonApiHttpException;
use AlexFigures\JsonApi\Http\Error\ErrorObject;
use Symfony\Component\HttpFoundation\RequestStack;

/** Deterministic demo credentials. Replace this adapter with your real authenticator. */
final class PublishingContext
{
    public function __construct(private RequestStack $requests, public readonly bool $enabled)
    {
    }

    public function identity(): PublishingIdentity
    {
        $token = $this->requests->getCurrentRequest()?->headers->get('Authorization');
        return match ($token) {
            'Bearer reader' => new PublishingIdentity('reader', 'ada@example.test'),
            'Bearer editor-a' => new PublishingIdentity('editor', 'ada@example.test'),
            'Bearer editor-b' => new PublishingIdentity('editor', 'grace@example.test'),
            'Bearer admin' => new PublishingIdentity('admin', ''),
            default => throw new JsonApiHttpException(401, 'A valid demo Bearer credential is required.', ['WWW-Authenticate' => 'Bearer'], [new ErrorObject(null, null, '401', 'authentication-required', 'Authentication required', 'Use a valid demo Bearer credential.', null)]),
        };
    }
}
