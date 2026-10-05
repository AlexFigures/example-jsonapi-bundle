<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

use AlexFigures\Symfony\Profile\ProfileInterface;
use AlexFigures\Symfony\Profile\Descriptor\ProfileDescriptor;
use AlexFigures\Symfony\Profile\Validation\ProfileRequirements;
use App\Security\PublishingContext;

/** Minimal consumer reproduction: profile services should support ordinary constructor DI. */
final class InjectableProfile implements ProfileInterface
{
    public function __construct(private PublishingContext $context) {}
    public function uri(): string { return 'urn:example:injectable-profile'; }
    public function descriptor(): ProfileDescriptor { return new ProfileDescriptor($this->uri(), 'Injected context', '1.0'); }
    public function hooks(): iterable { return []; }
    public function requirements(): ?ProfileRequirements { return null; }
}
