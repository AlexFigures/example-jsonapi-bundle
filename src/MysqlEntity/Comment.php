<?php

declare(strict_types=1);

namespace App\MysqlEntity;

use AlexFigures\JsonApi\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\JsonApi\Resource\Attribute\Id;
use AlexFigures\JsonApi\Resource\Attribute\JsonApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'comments')]
#[JsonApiResource(
    type: 'comments',
    normalizationContext: ['groups' => ['comment:read']],
    denormalizationContext: ['groups' => ['comment:write']]
)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Id]
    #[JsonApiAttribute]
    #[Groups(['comment:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[JsonApiAttribute]
    #[Groups(['comment:read', 'comment:write'])]
    #[Assert\Length(min: 3)]
    private string $body = '';

    #[ORM\Column(type: 'integer')]
    #[JsonApiAttribute]
    #[Groups(['comment:read', 'comment:write'])]
    private int $articleId = 0;

    #[ORM\Column(type: 'string', length: 255)]
    #[JsonApiAttribute]
    #[Groups(['comment:read', 'comment:write'])]
    #[Assert\Length(min: 2, max: 255)]
    private string $authorName = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }
}
