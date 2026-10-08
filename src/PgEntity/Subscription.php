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
#[ORM\Table(name: 'subscriptions')]
#[JsonApiResource(type: 'subscriptions', normalizationContext: ['groups' => ['subscriptions:read']], denormalizationContext: ['groups' => ['subscriptions:write']])]
class Subscription
{
    #[ORM\Id, ORM\Column(type: 'string', length: 36), Id]
    #[Groups(['subscriptions:read'])]
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    #[ORM\Column(length: 255, unique: true)]
    #[JsonApiAttribute, Groups(['subscriptions:read', 'subscriptions:write'])]
    #[Assert\NotBlank, Assert\Email]
    private string $email = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }


    public function __construct()
    {
        $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
    }

}
