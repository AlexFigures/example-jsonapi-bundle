<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\JsonApi\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class DocumentAsset extends Asset
{
    #[ORM\Column(length: 255), JsonApiAttribute]
    private string $filename = '';
    public function getFilename(): string { return $this->filename; }
}
