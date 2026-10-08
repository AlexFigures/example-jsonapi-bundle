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

#[ORM\Entity]
#[ORM\Table(name: 'audit_logs')]
#[JsonApiResource(type: 'audit-logs', operations: [\AlexFigures\JsonApi\Resource\Definition\ResourceOperation::INDEX, \AlexFigures\JsonApi\Resource\Definition\ResourceOperation::SHOW], normalizationContext: ['groups' => ['audit-logs:read']], denormalizationContext: ['groups' => ['audit-logs:write']])]
class AuditLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer'), Id]
    #[Groups(['audit-logs:read'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: 'text')]
    #[JsonApiAttribute, Groups(['audit-logs:read'])]
    private string $message = '';

    public function getMessage(): string
    {
        return $this->message;
    }

    #[ORM\Column(type: 'datetime_immutable')]
    #[JsonApiAttribute, Groups(['audit-logs:read'])]
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


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('2026-01-01T00:00:00+00:00');
    }

    public function record(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
