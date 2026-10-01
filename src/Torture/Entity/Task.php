<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_tasks')]
#[JsonApiResource(type: 'tasks')]
#[FilterableFields([new FilterableField('title', operators: ['eq', 'in', 'like']), new FilterableField('description', operators: ['eq', 'in', 'like']), new FilterableField('labels.name', operators: ['eq', 'in']), new FilterableField('attachments.name', operators: ['eq'])])]
#[SortableFields(['id', 'title', 'description', 'labels.name', 'attachments.name'])]
#[\Symfony\Component\Serializer\Attribute\Groups(['Default'])]
class Task
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint'), Id]
    private ?string $id = null;

    public function getId(): ?string { return $this->id; }

    #[ORM\Column(length: 255)]
    #[JsonApiAttribute, Assert\NotBlank]
    private string $title = '';

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): self { $this->title = $value; return $this; }

    #[ORM\Column(type: 'text')]
    #[JsonApiAttribute]
    private string $description = '';

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): self { $this->description = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[Relationship(targetType: 'projects')]
    private ?Project $project = null;

    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $value): self { $this->project = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: WorkspaceUser::class)]
    #[Relationship(targetType: 'workspace-users')]
    private ?WorkspaceUser $assignee = null;

    public function getAssignee(): ?WorkspaceUser { return $this->assignee; }
    public function setAssignee(?WorkspaceUser $value): self { $this->assignee = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'children')]
    #[Relationship(targetType: 'tasks')]
    private ?Task $parent = null;

    public function getParent(): ?Task { return $this->parent; }
    public function setParent(?Task $value): self { $this->parent = $value; return $this; }

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'parent')]
    #[Relationship(toMany: true, targetType: 'tasks')]
    private Collection $children;

    public function getChildren(): Collection { return $this->children; }
    public function addChild(Task $value): self { if (!$this->children->contains($value)) { $this->children->add($value); $value->setParent($this); } return $this; }
    public function removeChild(Task $value): self { if ($this->children->removeElement($value) && $value->getParent() === $this) { $value->setParent(null); } return $this; }

    #[ORM\ManyToMany(targetEntity: \App\PgEntity\Tag::class)]
    #[ORM\JoinTable(name: 'torture_task_labels')]
    #[Relationship(toMany: true, targetType: 'tags')]
    private Collection $labels;

    public function getLabels(): Collection { return $this->labels; }
    public function addLabel(\App\PgEntity\Tag $value): self { if (!$this->labels->contains($value)) { $this->labels->add($value); } return $this; }
    public function removeLabel(\App\PgEntity\Tag $value): self { $this->labels->removeElement($value); return $this; }

    #[ORM\ManyToMany(targetEntity: Asset::class)]
    #[ORM\JoinTable(name: 'torture_task_attachments')]
    #[Relationship(toMany: true, targetType: 'assets')]
    private Collection $attachments;

    public function getAttachments(): Collection { return $this->attachments; }
    public function addAttachment(Asset $value): self { if (!$this->attachments->contains($value)) { $this->attachments->add($value); } return $this; }
    public function removeAttachment(Asset $value): self { $this->attachments->removeElement($value); return $this; }

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->labels = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }
}
