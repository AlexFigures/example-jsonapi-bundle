<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class VideoAsset extends Asset
{
    #[ORM\Column(type: 'integer'), JsonApiAttribute]
    private int $duration = 0;
    public function getDuration(): int { return $this->duration; }
}
