<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_workspace_users')]
#[JsonApiResource(type: 'workspace-users')]
#[FilterableFields([new FilterableField('name', operators: ['eq', 'in', 'like'])])]
#[SortableFields(['id', 'name'])]
#[\Symfony\Component\Serializer\Attribute\Groups(['Default'])]
class WorkspaceUser
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    private string $id;

    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }

    #[ORM\Column(length: 120)]
    #[JsonApiAttribute, Assert\NotBlank]
    private string $name = '';

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'users')]
    #[Relationship(targetType: 'organizations')]
    private ?Organization $organization = null;

    public function getOrganization(): ?Organization { return $this->organization; }
    public function setOrganization(?Organization $value): self { $this->organization = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[Relationship(targetType: 'countries')]
    private ?Country $country = null;

    public function getCountry(): ?Country { return $this->country; }
    public function setCountry(?Country $value): self { $this->country = $value; return $this; }

    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
    }
}
