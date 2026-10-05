<?php

declare(strict_types=1);

namespace App\PgEntity;

use Doctrine\ORM\Mapping as ORM;

/** Association state belongs to the application and is not an API resource. */
#[ORM\Entity]
#[ORM\Table(name: 'feature_article_tags')]
class FeatureArticleTag
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    public ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: FeatureArticle::class, inversedBy: 'articleTags')]
        public FeatureArticle $article,
        #[ORM\ManyToOne(targetEntity: Tag::class)]
        public Tag $tag,
        #[ORM\Column] public int $position = 0,
    ) {
    }
}
