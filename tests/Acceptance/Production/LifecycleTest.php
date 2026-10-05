<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Production;

use App\Tests\Acceptance\Support\{ProductionTestCase, LifecycleProbe, ExpectedBundleGap};
use PHPUnit\Framework\Attributes\Group;

final class LifecycleTest extends ProductionTestCase
{
    public function testApplicationDomainEventAndTransactionalRecorder(): void
    {
        $this->client->disableReboot();
        $probe = self::getContainer()->get(LifecycleProbe::class);
        $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/publish'));
        self::assertCount(1, $probe->domain);
        // The application event deliberately runs inside the transaction before flush.
        self::assertTrue($probe->domain[0]['transaction_active']);
        self::assertSame('draft', $probe->domain[0]['committed_status']);
        self::assertSame(0, $probe->domain[0]['notification_count']);
        self::assertSame(1, $this->notificationCount());
        $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/publish'));
        self::assertCount(1, $probe->domain);
        self::assertSame(1, $this->notificationCount());
    }

    public function testBundleLifecycleNotificationIsObservable(): void
    {
        $this->client->disableReboot();
        $probe = self::getContainer()->get(LifecycleProbe::class);
        $this->decodeJsonApi($this->asUser('editor-a', 'POST', $this->url().'/publish'));
        self::assertCount(1, $probe->resource);
        self::assertSame('update', $probe->resource[0]['operation']);
        // Report timing without redefining an ordinary lifecycle event as a commit guarantee.
        file_put_contents(dirname(__DIR__, 3).'/var/production-lifecycle.json', json_encode($probe->resource, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
    }
}
