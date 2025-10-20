<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\Id;
use AlexFigures\Symfony\Resource\Attribute\JsonApiResource;
use AlexFigures\Symfony\Resource\Attribute\Relationship;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'articles')]
#[JsonApiResource(
    type: 'articles',
    normalizationContext: ['groups' => ['article:read']],
    denormalizationContext: ['groups' => ['article:write']]
)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Id]
    #[JsonApiAttribute]
    #[Groups(['article:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[JsonApiAttribute]
    #[Groups(['article:read', 'article:write'])]
    #[Assert\Length(min: 3, max: 255)]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    #[JsonApiAttribute]
    #[Groups(['article:read', 'article:write'])]
    private string $content = '';

    #[ORM\Column(type: 'datetime_immutable')]
    #[JsonApiAttribute]
    #[Groups(['article:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Relationship(targetType: 'authors')]
    #[Groups(['article:read', 'article:write'])]
    private ?Author $author = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
