<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\Id;
use AlexFigures\Symfony\Resource\Attribute\JsonApiResource;
use AlexFigures\Symfony\Resource\Attribute\Relationship;
use AlexFigures\Symfony\Resource\Attribute\FilterableFields;
use AlexFigures\Symfony\Resource\Attribute\FilterableField;
use AlexFigures\Symfony\Resource\Attribute\SortableFields;
use AlexFigures\Symfony\Resource\Attribute\SortableField;
use AlexFigures\Symfony\Resource\Metadata\RelationshipLinkingPolicy;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[FilterableFields([new FilterableField('search', operators: ['eq'], customHandler: \App\JsonApi\Filter\ArticleSearchFilter::class), new FilterableField('title', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('slug', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('status', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('published-at', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('views', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('featured', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull']), new FilterableField('author.name', operators: ['eq', 'ne', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'like', 'ilike', 'between', 'isnull', 'null', 'nnull'])])]
#[SortableFields(['id', 'title', 'createdAt', 'published-at', 'views', 'author.name'])]
#[\AlexFigures\Symfony\Profile\Attribute\Auditable]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'articles')]
#[JsonApiResource(type: 'articles', normalizationContext: ['groups' => ['articles:read']], denormalizationContext: ['groups' => ['articles:write']])]
#[\AlexFigures\Symfony\Resource\Attribute\JsonApiCustomRoute(name: 'articles.publish', path: '/api/articles/{id}/publish', methods: ['POST'], handler: \App\Application\Article\PublishArticle::class)]
class Article
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer'), Id]
    #[Groups(['articles:read'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255)]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    #[Assert\NotBlank, Assert\Length(min: 3, max: 255)]
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    #[ORM\Column(length: 255, unique: true)]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    #[Assert\NotBlank]
    private string $slug = '';

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    #[ORM\Column(type: 'text')]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    private string $content = '';

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    #[ORM\Column(type: 'string', enumType: \App\Enum\ArticleStatus::class)]
    #[JsonApiAttribute, Groups(['articles:read'])]
    private \App\Enum\ArticleStatus $status = \App\Enum\ArticleStatus::DRAFT;

    public function getStatus(): \App\Enum\ArticleStatus
    {
        return $this->status;
    }

    public function setStatus(\App\Enum\ArticleStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    #[ORM\Column(type: 'json')]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    private array $metadata = [];

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[JsonApiAttribute(name: 'published-at'), Groups(['articles:read'])]
    private ?\DateTimeImmutable $publishedAt = null;

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    #[ORM\Column(type: 'datetime_immutable')]
    #[JsonApiAttribute, Groups(['articles:read'])]
    private \DateTimeImmutable $createdAt;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\Column(type: 'datetime_immutable')]
    #[JsonApiAttribute, Groups(['articles:read'])]
    private \DateTimeImmutable $updatedAt;

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\Column(type: 'boolean')]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    private bool $featured = false;

    public function getFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    #[ORM\Column(type: 'integer')]
    #[JsonApiAttribute, Groups(['articles:read'])]
    #[Assert\PositiveOrZero]
    private int $views = 0;

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    #[ORM\Column(type: 'float')]
    #[JsonApiAttribute, Groups(['articles:read', 'articles:write'])]
    private float $rating = 0.0;

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Author $author = null;

    #[Relationship(targetType: 'authors', linkingPolicy: RelationshipLinkingPolicy::VERIFY, propertyPath: 'author')]
    #[Groups(['articles:read', 'articles:write'])]
    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $value): self
    {
        $this->author = $value;

        return $this;
    }
    #[ORM\ManyToOne(targetEntity: Author::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Author $editor = null;

    #[Relationship(targetType: 'authors', linkingPolicy: RelationshipLinkingPolicy::VERIFY, propertyPath: 'editor')]
    #[Groups(['articles:read', 'articles:write'])]
    public function getEditor(): ?Author
    {
        return $this->editor;
    }

    public function setEditor(?Author $value): self
    {
        $this->editor = $value;

        return $this;
    }
    /** @var Collection<int, Tag> */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[Relationship(toMany: true, targetType: 'tags'), Groups(['articles:read', 'articles:write'])]
    private Collection $tags;

    /** @return Collection<int, Tag> */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $item): self
    {
        if (!$this->tags->contains($item)) {
            $this->tags->add($item);
        }

        return $this;
    }

    public function removeTag(Tag $item): self
    {
        $this->tags->removeElement($item);

        return $this;
    }
    #[ORM\ManyToOne(targetEntity: Author::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Author $reviewer = null;

    #[Relationship(targetType: 'authors', linkingPolicy: RelationshipLinkingPolicy::VERIFY, propertyPath: 'reviewer')]
    #[Groups(['articles:read', 'articles:write'])]
    public function getReviewedBy(): ?Author
    {
        return $this->reviewer;
    }

    public function setReviewedBy(?Author $value): self
    {
        $this->reviewer = $value;

        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->tags = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** Returns false for an idempotent repeat; the original timestamp is preserved. */
    public function publish(\DateTimeImmutable $now): bool
    {
        if ($this->status === \App\Enum\ArticleStatus::PUBLISHED) {
            return false;
        }
        if ($this->status === \App\Enum\ArticleStatus::ARCHIVED) {
            throw new \App\Application\Article\PublicationRejected('Archived articles cannot be published.', 409);
        }
        if (trim($this->content) === '' || $this->author === null || strlen(trim($this->title)) < 3) {
            throw new \App\Application\Article\PublicationRejected('Publication requires a title, content and author.', 422);
        }
        $this->status = \App\Enum\ArticleStatus::PUBLISHED;
        $this->publishedAt = $now;
        $this->updatedAt = $now;

        return true;
    }

    public function getReviewer(): ?Author
    {
        return $this->reviewer;
    }

    public function setReviewer(?Author $reviewer): self
    {
        $this->reviewer = $reviewer;

        return $this;
    }
}
