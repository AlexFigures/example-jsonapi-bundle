<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Support;

final class ConcurrentPublishingRequests
{
    public static function run(array $requests): array
    {
        $workers = [];
        try {
            foreach ($requests as $request) {
                $process = proc_open([PHP_BINARY, dirname(__DIR__, 3).'/tools/publishing-worker.php'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
                if (!is_resource($process)) { throw new \RuntimeException('proc_open is required for the overlapping HTTP acceptance scenario.'); }
                stream_set_timeout($pipes[1], 20);
                $workers[] = ['process' => $process, 'pipes' => $pipes];
                fwrite($pipes[0], json_encode($request, JSON_THROW_ON_ERROR)."\n");
            }
            foreach ($workers as $worker) {
                if (trim((string) fgets($worker['pipes'][1])) !== 'READY') { throw new \RuntimeException('Concurrent worker did not reach its start barrier: '.stream_get_contents($worker['pipes'][2])); }
            }
            foreach ($workers as $worker) { fwrite($worker['pipes'][0], "GO\n"); fclose($worker['pipes'][0]); }
            $results = [];
            foreach ($workers as $worker) {
                $output = fgets($worker['pipes'][1]);
                if ($output === false) { throw new \RuntimeException('Concurrent HTTP request exceeded the 20-second deadline.'); }
                $results[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            }
            return $results;
        } finally {
            foreach ($workers as $worker) {
                foreach ($worker['pipes'] as $pipe) { if (is_resource($pipe)) { fclose($pipe); } }
                if (proc_get_status($worker['process'])['running']) { proc_terminate($worker['process']); }
                proc_close($worker['process']);
            }
        }
    }
}
