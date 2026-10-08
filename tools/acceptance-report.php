<?php

declare(strict_types=1);

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/report-evidence.php';
function writeReportFile(string $path, string $contents): void
{
    if (@file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Cannot write report '.$path);
    }
}

$root = dirname(__DIR__);
$junitPath = $argv[1] ?? $root.'/var/acceptance-junit.xml';
if (!is_file($junitPath)) {
    fwrite(STDERR, "Run the full suite with --log-junit var/acceptance-junit.xml first.\n");
    exit(2);
}
$inventory = json_decode(file_get_contents($root.'/docs/history/bundle-gaps.json'), true, 512, JSON_THROW_ON_ERROR);
$inventory['bundle_revision'] = Composer\InstalledVersions::getReference('alexfigures/symfony-jsonapi-bundle');
$gapById = array_column($inventory['gaps'], null, 'id');
$methods = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Acceptance'));
foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Test.php')) { continue; }
    $relative = substr($file->getPathname(), strlen($root.'/tests/Acceptance/'), -4);
    $class = 'App\\Tests\\Acceptance\\'.str_replace('/', '\\', $relative);
    $reflection = new ReflectionClass($class);
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->name, 'test')) { continue; }
        $groups = array_merge($reflection->getAttributes(Group::class), $method->getAttributes(Group::class));
        $isGap = in_array('bundle-gap', array_map(static fn (ReflectionAttribute $a): string => $a->newInstance()->name(), $groups), true);
        $markers = array_map(static fn (ReflectionAttribute $a): ExpectedBundleGap => $a->newInstance(), $method->getAttributes(ExpectedBundleGap::class));
        if ($isGap && $markers === []) {
            throw new RuntimeException("Group/expected-gap marker mismatch: $class::{$method->name}");
        }
        foreach ($markers as $marker) {
            if (!isset($gapById[$marker->id])) { throw new RuntimeException('Unknown gap '.$marker->id); }
        }
        $source = implode('', array_slice(file($file->getPathname()), $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
        preg_match_all('/(?<![\w-])([1-5]\d{2})(?![\w-])/', $source, $matches);
        $statuses = array_map('intval', $matches[1]);
        if (str_contains($source, 'decodeJsonApi(') || str_contains($source, 'collection(')) { $statuses[] = 200; }
        $datasets = [];
        foreach ($method->getAttributes(DataProvider::class) as $provider) {
            $providerMethod = $provider->newInstance()->methodName();
            foreach ($class::$providerMethod() as $key => $row) {
                $label = is_int($key) ? '#'.$key : $key;
                $datasets[$label] = $row;
            }
        }
        $methods[$class.'::'.$method->name] = compact('isGap', 'markers', 'statuses', 'datasets', 'method', 'relative');
    }
}
foreach ($inventory['gaps'] as $gap) {
    foreach ($gap['tests'] as $target) {
        [$relative, $methodName] = explode('::', $target['test']);
        $key = 'App\\Tests\\Acceptance\\'.str_replace('/', '\\', $relative).'::'.$methodName;
        if (!isset($methods[$key])) { throw new RuntimeException('Inventory references missing test '.$target['test']); }
    }
}
$expectedCases = array_sum(array_map(static fn (array $info): int => max(1, count($info['datasets'])), $methods));
$traces = [];
$tracePath = $root.'/var/acceptance-http.ndjson';
if (is_file($tracePath)) {
    foreach (file($tracePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $trace = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        $traces[$trace['test']] = $trace['requests'];
    }
}
$xml = simplexml_load_file($junitPath);
if ($xml === false) { throw new RuntimeException('Invalid JUnit XML.'); }
$counts = ['stable_pass' => 0, 'gap_pass' => 0, 'gap_fail' => 0, 'unexpected_fail' => 0, 'skipped' => 0];
$rows = [];
$executed = [];
foreach ($xml->xpath('//testcase') as $test) {
    $class = (string) $test['class'];
    $name = (string) $test['name'];
    $caseKey = $class.'::'.$name;
    if (isset($executed[$caseKey])) { throw new RuntimeException('Duplicate JUnit case '.$caseKey); }
    $executed[$caseKey] = true;
    $parts = explode(' with data set ', $name, 2);
    $methodName = $parts[0];
    $label = isset($parts[1]) ? trim($parts[1], '"') : '';
    $info = $methods[$class.'::'.$methodName] ?? throw new RuntimeException('Unknown test '.$class.'::'.$name);
    $gapIds = [];
    foreach ($info['markers'] as $marker) {
        if ($marker->datasets === [] || in_array($label, $marker->datasets, true)) { $gapIds[] = $marker->id; }
    }
    $historicalGapIds = [];
    foreach ($inventory['gaps'] as $gap) {
        foreach ($gap['tests'] as $target) {
            $targetKey = 'App\\Tests\\Acceptance\\'.str_replace('/', '\\', $target['test']);
            if ($targetKey === $class.'::'.$methodName && (!isset($target['datasets']) || in_array($label, $target['datasets'], true))) {
                $historicalGapIds[] = $gap['id'];
            }
        }
    }
    // A provider can mix regular cases with explicitly named gap datasets.
    // Only matching metadata classifies a failure as an expected bundle gap.
    $isGapCase = $gapIds !== [];
    $failure = isset($test->failure) || isset($test->error);
    $skipped = isset($test->skipped);
    ++$counts[$skipped ? 'skipped' : ($failure ? ($isGapCase ? 'gap_fail' : 'unexpected_fail') : ($isGapCase ? 'gap_pass' : 'stable_pass'))];
    $statuses = $info['statuses'];
    if (isset($info['datasets'][$label])) {
        foreach ($info['method']->getParameters() as $index => $parameter) {
            if ($parameter->name === 'status') { $statuses[] = $info['datasets'][$label][$index]; }
        }
    }
    $statuses = array_values(array_unique($statuses));
    sort($statuses);
    $requests = $traces[$class.'::'.$name] ?? [];
    $observed = array_values(array_unique(array_column($requests, 'status')));
    $area = explode('/', $info['relative'])[0];
    $failureText = $failure ? (string) ($test->failure ?? $test->error) : null;
    // Strip correlation IDs so committed reports remain readable and reproducible.
    $failureText = $failureText === null ? null : preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '<uuid>', $failureText);
    $rows[] = ['area' => $area, 'scenario' => $name, 'test' => $class.'::'.$name,
        'file' => 'tests/Acceptance/'.$info['relative'].'.php', 'expected_statuses' => $statuses,
        'current_statuses' => $observed, 'result' => $skipped ? 'SKIP' : ($failure ? 'FAIL' : 'PASS'),
        'bundle_gaps' => $gapIds, 'historical_gaps' => array_values(array_unique($historicalGapIds)), 'failure' => $failureText];
}
if (count($rows) !== $expectedCases) {
    throw new RuntimeException(sprintf('Incomplete JUnit: %d cases, expected %d. Run the full acceptance suite before generating reports.', count($rows), $expectedCases));
}
$gapSummary = [];
foreach ($inventory['gaps'] as $gap) {
    $matching = array_values(array_filter($rows, static fn (array $row): bool => in_array($gap['id'], $row['historical_gaps'], true)));
    if ($matching === []) { throw new RuntimeException('Historical gap has no executed regression assertion: '.$gap['id']); }
    $failures = array_values(array_filter($matching, static fn (array $row): bool => $row['result'] === 'FAIL'));
    $gapSummary[] = ['id' => $gap['id'], 'status' => $failures === [] ? 'RESOLVED_ON_TESTED_REVISION' : 'OPEN',
        'passing_cases' => count($matching) - count($failures), 'failing_cases' => count($failures),
        'current_failures' => array_column($failures, 'test')];
}
$report = ['evidence' => reportEvidence('acceptance', $junitPath, $tracePath), 'assertions' => array_sum(array_map(static fn ($test): int => (int) $test['assertions'], $xml->xpath('//testcase'))), 'bundle_revision' => $inventory['bundle_revision'], 'gap_summary' => $gapSummary, 'phpunit_version' => PHPUnit\Runner\Version::id(),
    'php_version' => PHP_VERSION, 'counts' => $counts, 'scenarios' => $rows];
writeReportFile($root.'/docs/acceptance-results.json', json_encode($report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
$escape = static fn (string $value): string => str_replace(["\n", '|'], [' ', '\\|'], $value);
$matrix = "# Acceptance coverage matrix\n\nGenerated by `composer acceptance:report` from full-suite JUnit, HTTP traces, and explicit gap metadata. Bundle revision: `{$inventory['bundle_revision']}`.\n\n";
$matrix .= "Stable passes: {$counts['stable_pass']}; gap failures: {$counts['gap_fail']}; tagged gap passes: {$counts['gap_pass']}; unexpected failures: {$counts['unexpected_fail']}; skipped: {$counts['skipped']}.\n\n";
$matrix .= "Expected statuses list assertions in each scenario, including follow-up requests. HTTP statuses are observations, not substituted expectations. A failing test may stop before later assertions.\n\n";
$matrix .= "| Area | Scenario | Test | Expected HTTP | Current result / HTTP | Bundle gap? |\n|---|---|---|---|---|---|\n";
foreach ($rows as $row) {
    $matrix .= '| '.$row['area'].' | '.$escape($row['scenario']).' | ['.basename($row['file']).'](../'.$row['file'].') | '.(implode(', ', $row['expected_statuses']) ?: 'See test assertions').' | '.$row['result'].' / '.(implode(', ', $row['current_statuses']) ?: 'trace not recorded').' | '.($row['bundle_gaps'] === [] ? 'No' : ($row['result'] === 'PASS' ? 'Resolved: ' : 'OPEN: ').implode(', ', $row['bundle_gaps']))." |\n";
}
writeReportFile($root.'/docs/acceptance-matrix.md', $matrix);
$gapDoc = "# Current Acceptance status\n\n`docs/history/bundle-gaps.json` is the reviewed inventory. Only active failing cases carry `#[Group('bundle-gap')]` and `#[ExpectedBundleGap('ID')]`; resolved tests keep their assertions and historical target references; they use normal assertions and are never skipped. This report validates the markers against the inventory.\n\n";
$gapDoc .= "Categories: `MUST_CONFORMANCE` and `SHOULD_CONFORMANCE` refer to normative JSON:API requirements; `DESIRED_CAPABILITY` is an intentional application contract; `OPTIONAL_FEATURE` is never a conformance failure merely because absent. `APPLICATION_POLICY` belongs to the application; `INFRASTRUCTURE_LIMIT` belongs to the runtime/database/distributed system; `DOCUMENTATION_GAP` describes documentation/contract drift or discoverability. `DX_GAP` describes public integration ergonomics/tooling. `CONFIG_IMPLEMENTATION_GAP` identifies accepted configuration with no corresponding runtime implementation.\n\n";
$gapDoc .= "Tested bundle `{$inventory['bundle_revision']}`, PHP ".PHP_VERSION.', PHPUnit '.PHPUnit\Runner\Version::id().".\n\n";
foreach ($inventory['gaps'] as $gap) {
    $matching = array_filter($rows, static fn (array $row): bool => in_array($gap['id'], $row['historical_gaps'], true));
    $failCount = count(array_filter($matching, static fn (array $row): bool => $row['result'] === 'FAIL'));
    if ($failCount === 0) { continue; }
    $gapDoc .= '## '.$gap['id'].' — '.$gap['area']."\n\n";
    $gapDoc .= '**'.$gap['category'].' · '.$gap['priority'].'**. Observed: '.$failCount.' failing / '.count($matching)." cases.\n\n";
    $gapDoc .= '- Expected: '.$gap['expected']."\n- Current on tested revision: ".($failCount === 0 ? 'PASS; historical gap resolved for all covered cases.' : 'OPEN; see observed failures in acceptance-results.json.')."\n- Historical baseline: ".$gap['current']."\n- Bundle change: ".$gap['bundle_change_required']."\n- Tests:\n";
    foreach ($gap['tests'] as $target) {
        [$testClass, $testMethod] = explode('::', $target['test']);
        $gapDoc .= '  - ['.$target['test'].'](../tests/Acceptance/'.$testClass.'.php)'.(isset($target['datasets']) ? ' ('.implode(', ', $target['datasets']).')' : '')."\n";
    }
    if (isset($gap['why_bundle'])) { $gapDoc .= '- Responsibility: '.$gap['why_bundle']."\n"; }
    $gapDoc .= "\n";
}
$gapDoc .= "Current observed failures are also summarized in [current-gaps.json](current-gaps.json). Closed investigations and reviewed IDs are in [history](history/README.md).\n";
writeReportFile($root.'/docs/acceptance-status.md', rtrim($gapDoc)."\n");
fwrite(STDOUT, json_encode($counts, JSON_THROW_ON_ERROR)."\n");
exit($counts['unexpected_fail'] > 0 || $counts['skipped'] > 0 ? 1 : 0);
