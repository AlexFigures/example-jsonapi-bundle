<?php

declare(strict_types=1);

namespace App\Torture\Composite;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute as JsonApiAttribute, Id, JsonApiResource};
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[JsonApiResource(type: 'regional-quotas')]
class RegionalQuota
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    public string $organization = 'org-1';
    #[ORM\Id, ORM\Column(length: 2)]
    public string $country = 'GE';
    #[ORM\Column(type: 'integer'), JsonApiAttribute]
    public int $limit = 100;
}
