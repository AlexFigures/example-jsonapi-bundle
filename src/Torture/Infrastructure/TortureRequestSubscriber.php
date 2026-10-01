<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use AlexFigures\Symfony\Http\Error\ErrorObject;
use AlexFigures\Symfony\Http\Exception\JsonApiHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{RequestEvent, ResponseEvent};

final class TortureRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array { return ['kernel.request' => ['begin', 4096], 'kernel.response' => ['finish', -4096]]; }

    public function begin(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) { return; }
        $request = $event->getRequest();
        RequestMetrics::start((string) $request->headers->get('X-Torture-Fault', ''));
        memory_reset_peak_usage();
        $request->attributes->set('_torture_start', hrtime(true));
        $request->attributes->set('_torture_baseline', memory_get_usage(true));
        $tenant = (string) $request->headers->get('X-Tenant-ID', 'tenant-a');
        if (!in_array($tenant, ['tenant-a', 'tenant-b'], true)) { self::reject(400, 'unknown-tenant', 'Unknown tenant.'); }
        // Only explicitly opted-in ingress tests use application size limits.
        $ingress = $request->headers->get('X-Torture-Policy') === 'ingress';
        if ($ingress && strlen($request->getContent()) > 1048576) { self::reject(413, 'body-limit', 'Request body exceeds 1 MiB.'); }
        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) { return; }
        $walk = function (array $node, string $key = '') use (&$walk, $tenant, $ingress): void {
            if (isset($node['meta']['tenant']) && $node['meta']['tenant'] !== $tenant) { self::reject(409, 'cross-shard-reference', 'Cross-shard references and Atomic operations are forbidden.'); }
            if ($ingress && $key === 'data' && array_is_list($node) && count($node) > 2000) { self::reject(413, 'linkage-limit', 'Relationship input exceeds 2000 identifiers.'); }
            foreach ($node as $name => $child) { if (is_array($child)) { $walk($child, (string) $name); } }
        };
        $walk($body);
    }

    public static function reject(int $status, string $code, string $detail): never
    {
        throw new JsonApiHttpException($status, $detail, errors: [new ErrorObject(null, null, (string) $status, $code, 'Application policy', $detail, null)]);
    }

    public function finish(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) { return; }
        $request = $event->getRequest();
        $response = $event->getResponse();
        $queries = RequestMetrics::$queries;
        $metrics = [
            'dataset' => json_decode((string) @file_get_contents(dirname(__DIR__, 3).'/var/torture/dataset.json'), true),
            'method' => $request->getMethod(), 'url' => $request->getRequestUri(), 'status' => $response->getStatusCode(),
            'wall_ms' => round((hrtime(true) - $request->attributes->get('_torture_start', hrtime(true))) / 1000000, 3),
            'query_count' => count($queries), 'unique_sql_count' => count(array_unique(array_column($queries, 'sql'))),
            'rows_fetched' => RequestMetrics::$rows, 'peak_bytes' => memory_get_peak_usage(true),
            'baseline_bytes' => $request->attributes->get('_torture_baseline'), 'response_bytes' => strlen((string) $response->getContent()),
            'databases' => array_values(array_unique(array_column($queries, 'database'))), 'transactions' => RequestMetrics::$transactions,
        ];
        $response->headers->set('X-Torture-Metrics', json_encode($metrics, JSON_THROW_ON_ERROR));
        RequestMetrics::$active = false;
        $directory = dirname(__DIR__, 3).'/var/torture';
        if (!is_dir($directory)) { mkdir($directory, 0775, true); }
        file_put_contents($directory.'/metrics.ndjson', json_encode($metrics + ['sql' => $queries], JSON_THROW_ON_ERROR)."\n", FILE_APPEND | LOCK_EX);
    }
}
