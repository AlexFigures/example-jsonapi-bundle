<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use Symfony\Component\Validator\Constraints as Assert;

final class FeatureArticleInput
{
    #[Assert\Length(min: 20)]
    public string $title = '';
}
