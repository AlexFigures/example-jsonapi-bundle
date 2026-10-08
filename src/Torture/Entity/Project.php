<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\JsonApi\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_projects')]
#[JsonApiResource(type: 'projects')]
#[FilterableFields([new FilterableField('name', operators: ['eq', 'in', 'like']), new FilterableField('memberships.role', operators: ['eq'])])]
#[SortableFields(['id', 'name', 'memberships.joinedAt'])]
#[\Symfony\Component\Serializer\Attribute\Groups(['Default'])]
class Project
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    private string $id;

    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }

    #[ORM\Column(length: 120, unique: true)]
    #[JsonApiAttribute, Assert\NotBlank]
    private string $name = '';

    public function getName(): string { return $this->name; }
    public function setName(string $value): self { $this->name = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'projects')]
    #[Relationship(targetType: 'organizations')]
    private ?Organization $organization = null;

    public function getOrganization(): ?Organization { return $this->organization; }
    public function setOrganization(?Organization $value): self { $this->organization = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: WorkspaceUser::class)]
    #[Relationship(targetType: 'workspace-users')]
    private ?WorkspaceUser $owner = null;

    public function getOwner(): ?WorkspaceUser { return $this->owner; }
    public function setOwner(?WorkspaceUser $value): self { $this->owner = $value; return $this; }

    #[ORM\OneToMany(targetEntity: ProjectMembership::class, mappedBy: 'project')]
    #[Relationship(toMany: true, targetType: 'project-memberships')]
    private Collection $memberships;

    public function getMemberships(): Collection { return $this->memberships; }
    public function addMembership(ProjectMembership $value): self { if (!$this->memberships->contains($value)) { $this->memberships->add($value); $value->setProject($this); } return $this; }
    public function removeMembership(ProjectMembership $value): self { if ($this->memberships->removeElement($value) && $value->getProject() === $this) { $value->setProject(null); } return $this; }

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'project')]
    #[Relationship(toMany: true, targetType: 'tasks')]
    private Collection $tasks;

    public function getTasks(): Collection { return $this->tasks; }
    public function addTask(Task $value): self { if (!$this->tasks->contains($value)) { $this->tasks->add($value); $value->setProject($this); } return $this; }
    public function removeTask(Task $value): self { if ($this->tasks->removeElement($value) && $value->getProject() === $this) { $value->setProject(null); } return $this; }

    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
        $this->memberships = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }
}
