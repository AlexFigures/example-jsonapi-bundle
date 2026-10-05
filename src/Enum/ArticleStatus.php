<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';
    case PUBLISHED = 'published';
}
