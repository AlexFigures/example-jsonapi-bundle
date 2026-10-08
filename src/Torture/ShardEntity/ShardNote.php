<?php

declare(strict_types=1);

namespace App\Torture\ShardEntity;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute as JsonApiAttribute, Id, JsonApiResource};
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/** A fixed-shard resource, used only to exercise two PostgreSQL transaction boundaries. */
#[ORM\Entity, ORM\Table(name: 'torture_shard_notes')]
#[JsonApiResource(type: 'shard-notes')]
#[Groups(['Default'])]
class ShardNote
{
    #[ORM\Id, ORM\Column(length: 64), Id]
    private string $id;
    #[ORM\Column(length: 120), JsonApiAttribute]
    private string $body = '';
    public function __construct() { $this->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122(); }
    public function getId(): string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }
    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }
}
