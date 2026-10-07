<?php

declare(strict_types=1);

use App\Tests\Torture\Support\ExpectedTortureGap;
use PHPUnit\Framework\Attributes\{DataProvider, Group};

require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/report-evidence.php';
$root = dirname(__DIR__);
$xmlPath = $argv[1] ?? $root.'/var/torture/junit.xml';
if (!is_file($xmlPath)) { throw new RuntimeException('Run the complete torture suite with --log-junit var/torture/junit.xml first.'); }
$inventory = json_decode(file_get_contents($root.'/docs/torture-gaps.json'), true, 512, JSON_THROW_ON_ERROR);
$gaps = array_column($inventory['gaps'], null, 'id');
$methods = [];
$expected = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Torture')) as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Test.php')) { continue; }
    $relative = substr($file->getPathname(), strlen($root.'/tests/Torture/'), -4);
    $class = 'App\\Tests\\Torture\\'.str_replace('/', '\\', $relative);
    $reflection = new ReflectionClass($class);
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->name, 'test')) { continue; }
        $groups = array_map(static fn (ReflectionAttribute $a): string => $a->newInstance()->name(), array_merge($reflection->getAttributes(Group::class), $method->getAttributes(Group::class)));
        $ids = array_map(static fn (ReflectionAttribute $a): string => $a->newInstance()->id, $method->getAttributes(ExpectedTortureGap::class));
        if (in_array('torture-gap', $groups, true) && $ids === []) { throw new RuntimeException('Gap group without metadata: '.$class.'::'.$method->name); }
        foreach ($ids as $id) { if (!isset($gaps[$id])) { throw new RuntimeException('Unknown gap '.$id); } }
        $cases = 1;
        foreach ($method->getAttributes(DataProvider::class) as $provider) {
            $providerName = $provider->newInstance()->methodName();
            $cases = 0;
            foreach ($class::$providerName() as $_) { ++$cases; }
        }
        $expected += $cases;
        $methods[$class.'::'.$method->name] = ['groups' => $groups, 'gap_ids' => $ids, 'file' => 'tests/Torture/'.$relative.'.php', 'area' => explode('/', $relative)[0]];
    }
}
$traces = [];
foreach (is_file($root.'/var/torture/scenarios.ndjson') ? file($root.'/var/torture/scenarios.ndjson', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $line) {
    $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
    $traces[$row['test']][] = $row['metric'];
}
$counts = ['PASS' => 0, 'BUNDLE_GAP' => 0, 'APPLICATION_POLICY' => 0, 'INFRASTRUCTURE_LIMIT' => 0, 'UNEXPECTED_FAILURE' => 0, 'SKIPPED' => 0];
$scenarios = [];
$executed = [];
foreach (simplexml_load_file($xmlPath)->xpath('//testcase') as $test) {
    $key = (string) $test['class'].'::'.(string) $test['name'];
    if (isset($executed[$key])) { throw new RuntimeException('Duplicate JUnit case '.$key); }
    $executed[$key] = true;
    $methodKey = explode(' with data set ', $key)[0];
    $info = $methods[$methodKey] ?? throw new RuntimeException('Unknown test '.$key);
    $info['historical_gap_ids'] = [];
    foreach ($gaps as $id => $gap) {
        foreach ($gap['regression_tests'] ?? [] as $target) {
            if ($target === $methodKey) { $info['historical_gap_ids'][] = $id; }
        }
    }
    $info['historical_gap_ids'] = array_values(array_unique(array_merge($info['historical_gap_ids'], $info['gap_ids'])));
    $failed = isset($test->failure) || isset($test->error);
    $classification = isset($test->skipped) ? 'SKIPPED' : ($failed ? (in_array('torture-gap', $info['groups'], true) ? 'BUNDLE_GAP' : 'UNEXPECTED_FAILURE') : (in_array('application-policy', $info['groups'], true) ? 'APPLICATION_POLICY' : (in_array('infrastructure-limit', $info['groups'], true) ? 'INFRASTRUCTURE_LIMIT' : 'PASS')));
    if ($failed && $info['gap_ids'] !== []) {
        $categories = array_column(array_intersect_key($gaps, array_flip($info['gap_ids'])), 'category');
        if (in_array('APPLICATION_POLICY', $categories, true)) { $classification = 'APPLICATION_POLICY'; }
        elseif (in_array('INFRASTRUCTURE_LIMIT', $categories, true)) { $classification = 'INFRASTRUCTURE_LIMIT'; }
    }
    ++$counts[$classification];
    $failure = $failed ? preg_replace('/[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}/i', '<uuid>', (string) ($test->failure ?? $test->error)) : null;
    $scenarios[] = $info + ['test' => $key, 'result' => isset($test->skipped) ? 'SKIP' : ($failed ? 'FAIL' : 'PASS'), 'classification' => $classification, 'failure' => $failure, 'http' => $traces[$key] ?? []];
}
if (count($scenarios) !== $expected) { throw new RuntimeException(sprintf('Incomplete report: %d of %d cases. Run the full suite.', count($scenarios), $expected)); }
foreach ($gaps as $id => &$gap) {
    $matching = array_values(array_filter($scenarios, static fn (array $s): bool => in_array($id, $s['historical_gap_ids'], true)));
    $gap['failing_cases'] = count(array_filter($matching, static fn (array $s): bool => $s['failure'] !== null));
    $gap['passing_cases'] = count($matching) - $gap['failing_cases'];
    $gap['status'] = $gap['failing_cases'] > 0 ? 'OPEN' : 'RESOLVED_ON_TESTED_REVISION';
    $gap['tests'] = array_column($matching, 'test');
    $gap['current_evidence'] = array_values(array_filter(array_column($matching, 'failure')));
}
unset($gap);
$revision = Composer\InstalledVersions::getReference('alexfigures/symfony-jsonapi-bundle');
$outcomes = array_count_values(array_column($scenarios, 'result'));
$report = ['evidence' => reportEvidence('torture', $xmlPath, $root.'/var/torture/scenarios.ndjson'), 'assertions' => array_sum(array_map(static fn ($test): int => (int) $test['assertions'], simplexml_load_file($xmlPath)->xpath('//testcase'))), 'outcomes' => $outcomes, 'generated_at' => gmdate(DATE_ATOM), 'bundle_version' => Composer\InstalledVersions::getPrettyVersion('alexfigures/symfony-jsonapi-bundle'), 'bundle_revision' => $revision, 'counts' => $counts, 'gaps' => array_values($gaps), 'scenarios' => $scenarios];
file_put_contents($root.'/docs/performance-results.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
$escape = static fn (string $text): string => str_replace(["\n", '|'], [' ', '\\|'], $text);
$markdown = "# Production torture results\n\nGenerated from complete JUnit and scenario HTTP metrics on bundle `$revision`.\n\n";
foreach ($outcomes as $name => $count) { $markdown .= '- HTTP test outcome '.$name.': '.$count."\n"; }
foreach ($counts as $name => $count) { $markdown .= "- $name: $count\n"; }
$markdown .= "\nWall times are observations, not CI thresholds. Memory is the PHP process peak since request start; baseline includes loaded classes. Rows fetched measures DBAL fetches, not exact ORM hydration. Routing records actual physical database names. Dataset counts exclude the extra BIGINT row.\n\n## Scenario matrix\n\n| Scenario | Result | HTTP | SQL counts | Wall ms | Peak MiB | Response bytes | Dataset tasks |\n|---|---|---|---|---|---|---|---|\n";
foreach ($scenarios as $scenario) {
    $http = $scenario['http'];
    $values = static fn (string $key): string => implode(', ', array_column($http, $key));
    $markdown .= '| ['.$escape(substr($scenario['test'], strlen('App\\Tests\\Torture\\'))).'](../'.$scenario['file'].') | '.$scenario['result'].' / '.$scenario['classification'].' | '.$values('status').' | '.$values('query_count').' | '.$values('wall_ms').' | '.implode(', ', array_map(static fn (array $m): float => round($m['peak_bytes'] / 1048576, 1), $http)).' | '.$values('response_bytes').' | '.implode(', ', array_map(static fn (array $m): int => $m['dataset']['tasks'] ?? 0, $http))." |\n";
}
$markdown .= "\n## Bundle gap inventory\n\nResolved markers are removed; regression assertions and historical test references remain. Failure details and physical connection/transaction metrics are in `performance-results.json`.\n\n";
foreach ($gaps as $gap) {
    $markdown .= '### '.$gap['id'].' — '.$gap['status']."\n\n".$gap['category'].' · '.$gap['priority'].' · '.$gap['failing_cases']." failing cases.\n\nExpected: ".$gap['expected']."\n\nBundle subsystem: ".$gap['subsystem'].".\n\n";
    if (isset($gap['current'])) { $markdown .= 'Current interpretation: '.$gap['current']."\n\n"; }
    if (isset($gap['interpretation'])) { $markdown .= $gap['interpretation']."\n\n"; }
    foreach ($gap['tests'] as $test) { $markdown .= '- `'.$test."`\n"; }
    $markdown .= "\n";
}
file_put_contents($root.'/docs/torture-results.md', $markdown);
fwrite(STDOUT, json_encode($counts, JSON_THROW_ON_ERROR)."\n");
exit($counts['UNEXPECTED_FAILURE'] > 0 || $counts['SKIPPED'] > 0 ? 1 : 0);
