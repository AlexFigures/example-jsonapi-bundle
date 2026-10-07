<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use Symfony\Component\Validator\Constraints as Assert;

/** An independent PATCH contract: no create-only required inputs. */
final class FeatureArticleUpdateInput
{
    #[Assert\Length(min: 5)]
    public string $title;
}
