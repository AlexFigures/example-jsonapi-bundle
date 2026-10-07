<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource};
use AlexFigures\Symfony\Profile\Attribute\Auditable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity, ORM\Table(name: 'feature_audit_notes')]
#[Auditable(createdAtField: 'insertedAt', updatedAtField: 'modifiedAt', createdByField: 'insertedBy', updatedByField: 'modifiedBy')]
#[JsonApiResource(type: 'feature-audit-notes', normalizationContext: ['groups' => ['audit-note:read']], denormalizationContext: ['groups' => ['audit-note:write']])]
class FeatureAuditNote
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column, Id, Groups(['audit-note:read'])]
    public ?int $id = null;
    #[ORM\Column(length: 255), Attribute, Groups(['audit-note:read', 'audit-note:write'])]
    public string $title = '';
    #[ORM\Column, Attribute, Groups(['audit-note:read'])]
    public \DateTimeImmutable $createdAt;
    #[ORM\Column, Attribute, Groups(['audit-note:read'])]
    public \DateTimeImmutable $updatedAt;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?string $createdBy = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?string $updatedBy = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?\DateTimeImmutable $insertedAt = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?\DateTimeImmutable $modifiedAt = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?string $insertedBy = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['audit-note:read'])]
    public ?string $modifiedBy = null;
    public function __construct() { $this->createdAt = $this->updatedAt = new \DateTimeImmutable('2000-01-01'); }
}
