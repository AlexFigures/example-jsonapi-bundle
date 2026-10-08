<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Discovery\Duplicate;

use AlexFigures\JsonApi\Resource\Attribute\{Attribute, Id, JsonApiResource};

#[JsonApiResource(type: 'articles')]
final class DuplicateArticle { #[Id] public string $id = 'duplicate'; }
