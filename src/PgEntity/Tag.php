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

#[FilterableFields([new FilterableField('name', operators: ['eq', 'ne', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull'])])]
#[SortableFields(['id', 'name'])]
#[ORM\Entity]
#[ORM\Table(name: 'tags')]
#[JsonApiResource(type: 'tags', normalizationContext: ['groups' => ['tags:read']], denormalizationContext: ['groups' => ['tags:write']])]
class Tag
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer'), Id]
    #[Groups(['tags:read'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 80)]
    #[JsonApiAttribute, Groups(['tags:read', 'tags:write'])]
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

    /** @var Collection<int, Article> */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'tags')]
    #[Relationship(toMany: true, targetType: 'articles'), Groups(['tags:read', 'tags:write'])]
    private Collection $articles;

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $item): self
    {
        if (!$this->articles->contains($item)) {
            $this->articles->add($item);
            $item->addTag($this);
        }

        return $this;
    }

    public function removeArticle(Article $item): self
    {
        $this->articles->removeElement($item);

        return $this;
    }

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

}
