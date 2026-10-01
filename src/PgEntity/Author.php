<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\Attribute;
use AlexFigures\Symfony\Resource\Attribute\Id;
use AlexFigures\Symfony\Resource\Attribute\JsonApiResource;
use AlexFigures\Symfony\Resource\Attribute\Relationship;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'authors')]
#[JsonApiResource(
    type: 'authors',
    normalizationContext: ['groups' => ['author:read']],
    denormalizationContext: ['groups' => ['author:write']]
)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Id]
    #[Attribute]
    #[Groups(['author:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Attribute]
    #[Groups(['author:read', 'author:write'])]
    #[Assert\Length(min: 2, max: 255)]
    private string $name = '';

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class, cascade: ['persist'], orphanRemoval: true)]
    #[Relationship(toMany: true, targetType: 'articles', inverse: 'author')]
    #[Groups(['author:read'])]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article) && $article->getAuthor() === $this) {
            $article->setAuthor(null);
        }

        return $this;
    }
}
