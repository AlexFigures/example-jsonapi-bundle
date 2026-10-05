<?php

declare(strict_types=1);

namespace App\Api\Cookbook;

final readonly class FeatureArticleView
{
    public function __construct(public int $id, public string $title)
    {
    }
}
