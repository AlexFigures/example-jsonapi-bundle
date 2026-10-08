<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Protocol;

use App\Controller\ErrorLinksController;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class ErrorTypeLinksTest extends AcceptanceTestCase
{
    #[DataProvider('variants')]
    public function testApplicationErrorTypeLinkIsSerializedOnEachError(string $variant, int $count): void
    {
        $document = $this->assertJsonApiError($this->requestJsonApi('GET', '/api/cookbook/error-links/'.$variant), 422);
        self::assertCount($count, $document['errors']);
        foreach ($document['errors'] as $error) {
            self::assertSame(ErrorLinksController::TYPE, $error['links']['type'] ?? null,
                'The problem type belongs to errors[].links.type, not top-level document links.');
            if ($variant === 'both') {
                self::assertSame(ErrorLinksController::ABOUT, $error['links']['about'] ?? null);
            } else {
                self::assertArrayNotHasKey('about', $error['links']);
            }
        }
    }

    public static function variants(): iterable
    {
        yield 'type only' => ['type', 1];
        yield 'type and occurrence are distinct' => ['both', 1];
        yield 'multiple validation errors' => ['multiple', 2];
    }

    public function testTypeLinkIsOptionalWhenApplicationDoesNotSupplyIt(): void
    {
        $document = $this->assertJsonApiError($this->requestJsonApi('GET', '/api/cookbook/error-links/omitted'), 422);
        self::assertArrayNotHasKey('type', $document['errors'][0]['links'] ?? []);
    }
}
