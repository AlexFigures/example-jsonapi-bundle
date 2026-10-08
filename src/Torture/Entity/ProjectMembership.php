<?php

declare(strict_types=1);

namespace App\Torture\Entity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\JsonApi\Resource\Attribute\{Id, JsonApiResource, Relationship, FilterableFields, FilterableField, SortableFields};
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'torture_project_memberships')]
#[JsonApiResource(type: 'project-memberships')]
#[FilterableFields([new FilterableField('role', operators: ['eq', 'in', 'like'])])]
#[SortableFields(['id', 'role'])]
#[\Symfony\Component\Serializer\Attribute\Groups(['Default'])]
class ProjectMembership
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    private string $id;

    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }

    #[ORM\Column(length: 30)]
    #[JsonApiAttribute, Assert\Choice(['owner', 'editor', 'reader'])]
    private string $role = 'reader';

    public function getRole(): string { return $this->role; }
    public function setRole(string $value): self { $this->role = $value; return $this; }

    #[ORM\Column(type: 'datetime_immutable')]
    #[JsonApiAttribute]
    private \DateTimeImmutable $joinedAt;

    public function getJoinedAt(): \DateTimeImmutable { return $this->joinedAt; }
    public function setJoinedAt(\DateTimeImmutable $value): self { $this->joinedAt = $value; return $this; }

    #[ORM\Column(type: 'json')]
    #[JsonApiAttribute]
    private array $notificationSettings = [];

    public function getNotificationSettings(): array { return $this->notificationSettings; }
    public function setNotificationSettings(array $value): self { $this->notificationSettings = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'memberships')]
    #[Relationship(targetType: 'projects')]
    private ?Project $project = null;

    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $value): self { $this->project = $value; return $this; }

    #[ORM\ManyToOne(targetEntity: WorkspaceUser::class)]
    #[Relationship(targetType: 'workspace-users')]
    private ?WorkspaceUser $user = null;

    public function getUser(): ?WorkspaceUser { return $this->user; }
    public function setUser(?WorkspaceUser $value): self { $this->user = $value; return $this; }

    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
        $this->joinedAt = new \DateTimeImmutable('now');
    }
}
