<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Discovery\Invalid;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute, Id, JsonApiResource};

#[JsonApiResource(type: 'invalid-discovery')]
final class InvalidArticle
{
    #[Id] public string $id = 'invalid';
    #[Attribute(name: 'title')] public string $first = '';
    #[Attribute(name: 'title')] public string $second = '';
}
