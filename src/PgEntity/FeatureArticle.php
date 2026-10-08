<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute;
use AlexFigures\JsonApi\Resource\Attribute\FilterableField;
use AlexFigures\JsonApi\Resource\Attribute\FilterableFields;
use AlexFigures\JsonApi\Resource\Attribute\Id;
use AlexFigures\JsonApi\Resource\Attribute\JsonApiResource;
use AlexFigures\JsonApi\Resource\Attribute\Relationship;
use AlexFigures\JsonApi\Resource\Attribute\SortableField;
use AlexFigures\JsonApi\Resource\Attribute\SortableFields;
use App\JsonApi\Sort\TitleLengthSort;
use Doctrine\ORM\Mapping as ORM;

/** Isolated metadata cookbook resource; the production Article contract stays unchanged. */
#[ORM\Entity]
#[ORM\Table(name: 'feature_articles')]
#[JsonApiResource(type: 'feature-articles', relationshipPolicies: ['source' => \AlexFigures\JsonApi\Resource\Metadata\RelationshipLinkingPolicy::VERIFY], normalizationContext: ['groups' => ['feature:read']], denormalizationContext: ['groups' => ['feature:write']], writeRequests: ['create' => \App\Api\Cookbook\FeatureArticleInput::class, 'update' => \App\Api\Cookbook\FeatureArticleUpdateInput::class], versionResolver: \App\Api\Cookbook\FeatureVersionResolver::class)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.read', path: '/cookbook/features/{id}/read', handler: \App\Api\Cookbook\FeatureReadHandler::class)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.query', path: '/cookbook/features-query', handler: \App\Api\Cookbook\FeatureReadHandler::class)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.command', path: '/cookbook/features/{id}/command/{form}', methods: ['POST'], handler: \App\Api\Cookbook\FeatureCommandHandler::class)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.scoped', path: '/cookbook/features/{id}/scoped/{scope}', handler: \App\Api\Cookbook\ScopedTransactionHandler::class)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.options', path: '/cookbook/route-options/{id}/{mode}', handler: \App\Api\Cookbook\FeatureReadHandler::class, defaults: ['mode' => 'normal'], requirements: ['id' => '\\d+', 'mode' => '[a-z]+'], description: 'Read with route defaults and requirements', priority: 20)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.priority', path: '/api/feature-articles/priority', handler: \App\Api\Cookbook\FeatureReadHandler::class, priority: 50)]
#[\AlexFigures\JsonApi\Resource\Attribute\JsonApiCustomRoute(name: 'cookbook.legacy', path: '/cookbook/legacy/{id}', controller: \App\Controller\FeatureLegacyController::class)]
#[FilterableFields([
    new FilterableField('source', inherit: true),
    new FilterableField('priority-search', operators: ['eq']),
    new FilterableField('title', operators: ['eq', 'starts_with']),
    new FilterableField('author', inherit: true),
    new FilterableField('tags', inherit: true),
    new FilterableField('editor', inherit: true, except: ['email']),
])]
#[SortableFields([
    'id', 'title', 'priority-sort',
    new SortableField('source', inherit: true),
    new SortableField('author', inherit: true),
    new SortableField('tags', inherit: true),
    new SortableField('editor', inherit: true, except: ['email']),
    new SortableField('title-length', customHandler: TitleLengthSort::class),
])]
class FeatureArticle
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column, Id]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read'])]
    public ?int $id = null;

    #[ORM\Column(length: 255), Attribute]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read', 'feature:write'])]
    public string $title = '';

    #[ORM\Column(length: 255)]
    public string $subtitle = 'Alternate representation';

    /** @var \Doctrine\Common\Collections\Collection<int, FeatureArticleTag> */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: FeatureArticleTag::class, cascade: ['persist', 'remove'])]
    public \Doctrine\Common\Collections\Collection $articleTags;

    public function __construct()
    {
        $this->articleTags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[Relationship(toMany: true, targetType: 'tags', propertyPath: 'articleTags.tag')]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read'])]
    public function getTags(): \Doctrine\Common\Collections\Collection
    {
        return $this->articleTags->map(static fn (FeatureArticleTag $link): Tag => $link->tag);
    }

    #[ORM\ManyToOne(targetEntity: Author::class), Relationship(targetType: 'authors', linkingPolicy: \AlexFigures\JsonApi\Resource\Metadata\RelationshipLinkingPolicy::REFERENCE)]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read', 'feature:write'])]
    public ?Author $author = null;

    #[ORM\ManyToOne(targetEntity: Article::class), Relationship(targetType: 'articles')]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read', 'feature:write'])]
    public ?Article $source = null;

    #[Relationship(targetType: 'authors', propertyPath: 'author')]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read'])]
    public function getEditor(): ?Author
    {
        return $this->author;
    }
    /** A computed association: suggested authors are application policy, not an ORM mapping. */
    #[Relationship(toMany: true, targetType: 'authors')]
    #[\Symfony\Component\Serializer\Attribute\Groups(['feature:read'])]
    public function getSuggestedAuthors(): array
    {
        return $this->author === null ? [] : [$this->author];
    }

}
