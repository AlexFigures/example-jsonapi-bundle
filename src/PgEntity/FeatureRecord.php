<?php

declare(strict_types=1);

namespace App\PgEntity;

use AlexFigures\Symfony\Resource\Attribute\{Attribute, Id, JsonApiResource, Relationship};
use AlexFigures\Symfony\Resource\Definition\ResourceOperation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/** Isolated operation-selection fixture, deliberately without collection reads or deletes. */
#[ORM\Entity]
#[ORM\Table(name: 'feature_records')]
#[JsonApiResource(type: 'feature-records', operations: [ResourceOperation::SHOW, ResourceOperation::CREATE, ResourceOperation::UPDATE], normalizationContext: ['groups' => ['record:read']], denormalizationContext: ['groups' => ['record:write']])]
class FeatureRecord
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column, Id, Groups(['record:read'])]
    public ?int $id = null;
    #[ORM\Column(length: 80), Attribute, Groups(['record:read', 'record:write'])]
    public string $title = '';
    #[ORM\ManyToOne(targetEntity: FeatureArticle::class), Relationship(targetType: 'feature-articles'), Groups(['record:read', 'record:write'])]
    public ?FeatureArticle $article = null;

}
