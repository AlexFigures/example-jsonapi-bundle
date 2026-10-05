<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;

final class DocumentBudgetTest extends AcceptanceTestCase
{
    protected function environment(): string { return 'features_document_budget'; }
    public function testFieldBudgetAtLimitStillAllowsConnectedInclude(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $this->url().'?include=author&fields[articles]=title,author&fields[authors]=name'));
        self::assertCount(1, $doc['included']);
        self::assertSame(['title'], array_keys($doc['data']['attributes']));
    }
    public function testFieldBudgetRejectsCumulativeFieldsetsAboveLimit(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url().'?include=author&fields[articles]=title,author,content&fields[authors]=name'), 400);
    }
    public function testIncludedResourceBudgetRejectsAboveLimitWithoutTruncation(): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', $this->url().'?include=author,tags'), 400);
    }
}
