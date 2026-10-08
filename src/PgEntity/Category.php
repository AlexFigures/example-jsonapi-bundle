<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\JsonApi\Resource\Attribute\Id;
use AlexFigures\JsonApi\Resource\Attribute\JsonApiResource;
use AlexFigures\JsonApi\Resource\Attribute\Relationship;
use AlexFigures\JsonApi\Resource\Attribute\FilterableFields;
use AlexFigures\JsonApi\Resource\Attribute\FilterableField;
use AlexFigures\JsonApi\Resource\Attribute\SortableFields;
use AlexFigures\JsonApi\Resource\Attribute\SortableField;
use AlexFigures\JsonApi\Resource\Metadata\RelationshipLinkingPolicy;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[SortableFields(['id', 'name'])]
#[\AlexFigures\JsonApi\Profile\Attribute\SoftDeletable(deletedByField: 'removedBy')]
#[ORM\Entity]
#[ORM\Table(name: 'categories')]
#[JsonApiResource(type: 'categories', normalizationContext: ['groups' => ['categories:read']], denormalizationContext: ['groups' => ['categories:write']])]
class Category
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer'), Id]
    #[Groups(['categories:read'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 100)]
    #[JsonApiAttribute, Groups(['categories:read', 'categories:write'])]
    #[Assert\NotBlank]
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $parent = null;

    #[Relationship(targetType: 'categories', linkingPolicy: RelationshipLinkingPolicy::VERIFY, propertyPath: 'parent')]
    #[Groups(['categories:read', 'categories:write'])]
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $value): self
    {
        $this->parent = $value;

        return $this;
    }
    /** @var Collection<int, Category> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Category::class)]
    #[Relationship(toMany: true, targetType: 'categories'), Groups(['categories:read', 'categories:write'])]
    private Collection $children;

    /** @return Collection<int, Category> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Category $item): self
    {
        if (!$this->children->contains($item)) {
            $this->children->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    public function removeChild(Category $item): self
    {
        $this->children->removeElement($item);

        return $this;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[JsonApiAttribute, Groups(['categories:read'])]
    private ?\DateTimeImmutable $deletedAt = null;

    /** Deletion actor is server-owned application audit data. */
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $removedBy = null;

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
