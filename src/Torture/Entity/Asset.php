<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_assets')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'kind', type: 'string')]
#[ORM\DiscriminatorMap(['document' => DocumentAsset::class, 'video' => VideoAsset::class])]
#[JsonApiResource(type: 'assets')]
abstract class Asset
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    protected string $id;
    #[ORM\Column(length: 120), JsonApiAttribute]
    protected string $name = '';
    public function __construct() { $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122(); }
    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
}
