<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\Attribute as JsonApiAttribute;
use AlexFigures\Symfony\Resource\Attribute\Id;
use AlexFigures\Symfony\Resource\Attribute\JsonApiResource;
use AlexFigures\Symfony\Resource\Attribute\Relationship;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[JsonApiResource(type: 'newsletters', normalizationContext: ['groups' => ['newsletters:read']], denormalizationContext: ['groups' => ['newsletters:write']])]
class Newsletter
{
    #[ORM\Id, ORM\Column(type: 'uuid'), Id]
    #[Groups(['newsletters:read'])]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[JsonApiAttribute, Groups(['newsletters:read', 'newsletters:write'])]
    #[Assert\NotBlank]
    private string $subject = '';

    #[ORM\ManyToOne(targetEntity: Subscription::class)]
    private ?Subscription $recipient = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $previousEdition = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    #[Relationship(targetType: 'subscriptions', propertyPath: 'recipient')]
    #[Groups(['newsletters:read', 'newsletters:write'])]
    public function getRecipient(): ?Subscription
    {
        return $this->recipient;
    }

    public function setRecipient(?Subscription $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    #[Relationship(targetType: 'newsletters', propertyPath: 'previousEdition')]
    #[Groups(['newsletters:read', 'newsletters:write'])]
    public function getPreviousEdition(): ?self
    {
        return $this->previousEdition;
    }

    public function setPreviousEdition(?self $previousEdition): self
    {
        $this->previousEdition = $previousEdition;

        return $this;
    }
}
