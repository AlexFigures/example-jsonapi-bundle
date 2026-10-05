<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource};
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[\AlexFigures\Symfony\Profile\Attribute\Auditable]
#[\AlexFigures\Symfony\Profile\Attribute\SoftDeletable(deletedAtField: 'deleted')]
#[ORM\Entity]
#[ORM\Table(name: 'feature_memos')]
#[JsonApiResource(type: 'feature-memos', normalizationContext: ['groups' => ['memo:read']], denormalizationContext: ['groups' => ['memo:write']])]
class FeatureMemo
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column, Id, Groups(['memo:read'])]
    public ?int $id = null;

    #[ORM\Column, Attribute, Groups(['memo:read'])]
    public bool $deleted = false;

    #[ORM\Column, Attribute, Groups(['memo:read'])]
    public \DateTimeImmutable $createdAt;
    #[ORM\Column, Attribute, Groups(['memo:read'])]
    public \DateTimeImmutable $updatedAt;
    #[ORM\Column(nullable: true), Attribute, Groups(['memo:read'])]
    public ?string $createdBy = null;
    #[ORM\Column(nullable: true), Attribute, Groups(['memo:read'])]
    public ?string $updatedBy = null;

    public function __construct(
        #[ORM\Column(length: 255), Attribute, Groups(['memo:read', 'memo:write']), Assert\NotBlank, Assert\Length(min: 5, groups: ['memo:write']), Assert\Length(min: 8, groups: ['create']), Assert\Length(max: 60, groups: ['update'])]
        public string $title,
        #[ORM\Column(length: 40), Attribute, Groups(['memo:read'])]
        public string $state = 'draft',
    ) {
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }
}
