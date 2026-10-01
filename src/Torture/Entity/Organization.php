<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_organizations')]
#[JsonApiResource(type: 'organizations')]
#[FilterableFields([new FilterableField('name', operators: ['eq', 'in', 'like'])])]
#[SortableFields(['id', 'name'])]
#[\Symfony\Component\Serializer\Attribute\Groups(['Default'])]
class Organization
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

    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'organization')]
    #[Relationship(toMany: true, targetType: 'projects')]
    private Collection $projects;

    public function getProjects(): Collection { return $this->projects; }
    public function addProject(Project $value): self { if (!$this->projects->contains($value)) { $this->projects->add($value); $value->setOrganization($this); } return $this; }
    public function removeProject(Project $value): self { if ($this->projects->removeElement($value) && $value->getOrganization() === $this) { $value->setOrganization(null); } return $this; }

    #[ORM\OneToMany(targetEntity: WorkspaceUser::class, mappedBy: 'organization')]
    #[Relationship(toMany: true, targetType: 'workspace-users')]
    private Collection $users;

    public function getUsers(): Collection { return $this->users; }
    public function addUser(WorkspaceUser $value): self { if (!$this->users->contains($value)) { $this->users->add($value); $value->setOrganization($this); } return $this; }
    public function removeUser(WorkspaceUser $value): self { if ($this->users->removeElement($value) && $value->getOrganization() === $this) { $value->setOrganization(null); } return $this; }

    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
        $this->projects = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
}
