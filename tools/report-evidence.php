<?php

declare(strict_types=1);

/** Validate that complete JUnit and HTTP traces belong to the tested dependency. */
function reportEvidence(string $suite, string $junit, string $trace): array
{
    $path = dirname(__DIR__).'/var/release-evidence.json';
    if (!is_file($path)) {
        return ['verified' => false, 'reason' => 'No release-run manifest; use the release gate runner.'];
    }
    $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $revision = Composer\InstalledVersions::getReference('alexfigures/symfony-jsonapi-bundle');
    $evidence = $manifest['suites'][$suite] ?? throw new RuntimeException('Missing evidence for '.$suite);
    if ($manifest['bundle_revision'] !== $revision || $evidence['junit_sha256'] !== hash_file('sha256', $junit) || $evidence['trace_sha256'] !== hash_file('sha256', $trace)) {
        throw new RuntimeException('Stale/mixed release evidence: rerun the release gate before reporting '.$suite);
    }
    return ['verified' => true, 'bundle_revision' => $revision] + $evidence;
}
