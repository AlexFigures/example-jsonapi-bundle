<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

use Symfony\Component\Validator\Constraints as Assert;

final class FeatureArticleInput
{
    #[Assert\Length(min: 20)]
    public string $title = '';

    /** Existing resource association; generic linkage resolution stays in the bundle. */
    public ?\App\PgEntity\Author $author = null;
}
