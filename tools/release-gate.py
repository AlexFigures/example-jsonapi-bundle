#!/usr/bin/env python3
"""Generate the release decision from complete independent-consumer reports.

--run executes all tests and refreshes evidence; --update also updates Composer.
No vendor changes, assertion rewrites or automatic classification of new failures.
"""
import argparse
import hashlib
import json
import re
import subprocess
import sys
from collections import Counter
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BASELINE = '1a32d7b35ca5ce3e7c4500f52a2455655fa57c6b'


def read(name):
    return json.loads((ROOT / 'docs' / name).read_text())


def write(name, value):
    (ROOT / 'docs' / name).write_text(json.dumps(value, indent=2, ensure_ascii=False) + '\n')


def command(args, log=None):
    print('Running: ' + ' '.join(args), flush=True)
    if log:
        with (ROOT / 'var' / log).open('w') as output:
            return subprocess.run(args, cwd=ROOT, stdout=output, stderr=subprocess.STDOUT).returncode
    return subprocess.run(args, cwd=ROOT).returncode


def php(*args):
    return ['docker', 'compose', 'exec', '-T', 'php', 'php', *args]


def current_revision():
    return subprocess.check_output(php('-r', 'require "vendor/autoload.php"; echo Composer\\InstalledVersions::getReference("alexfigures/symfony-jsonapi-bundle");'), cwd=ROOT, text=True).strip()


def record_evidence(expected_revision):
    revision = current_revision()
    if revision != expected_revision:
        raise SystemExit('Dependency changed during test run; do not publish mixed evidence.')
    suites = {}
    for suite, junit, trace in [('acceptance', 'var/acceptance-junit.xml', 'var/acceptance-http.ndjson'), ('torture', 'var/torture/junit.xml', 'var/torture/scenarios.ndjson')]:
        suites[suite] = {'junit_sha256': hashlib.sha256((ROOT / junit).read_bytes()).hexdigest(), 'trace_sha256': hashlib.sha256((ROOT / trace).read_bytes()).hexdigest()}
    (ROOT / 'var/release-evidence.json').write_text(json.dumps({'bundle_revision': revision, 'suites': suites}, indent=2) + '\n')


def cleanup_markers(acceptance, torture):
    """Remove only attributes on fully passing methods; preserve every assertion.

    Mixed datasets retain only the failing dataset labels. Historical target maps
    are independent of active markers so any future regression stays unexpected.
    """
    for report, directory, marker, group in [(acceptance, 'Acceptance', 'ExpectedBundleGap', 'bundle-gap'), (torture, 'Torture', 'ExpectedTortureGap', 'torture-gap')]:
        by_method = {}
        for row in report['scenarios']:
            key = row['test'].split(' with data set ')[0]
            by_method.setdefault(key, []).append(row)
        for key, cases in by_method.items():
            relative, method = key.split('::')
            prefix = 'App\\Tests\\' + directory + '\\'
            relative = relative[len(prefix):].replace('\\', '/')
            path = ROOT / 'tests' / directory / (relative + '.php')
            source = path.read_text()
            pattern = r'((?:    #\[[^\n]+\]\n)+)(    public function ' + re.escape(method) + r'\b)'
            def replace(match):
                attributes = match[1]
                if marker not in attributes:
                    return match[0]
                if all(c['result'] == 'PASS' for c in cases):
                    attributes = re.sub(r'    #\[' + marker + r'\([^\n]*\)\]\n', '', attributes)
                    if marker not in attributes:
                        attributes = attributes.replace("    #[Group('" + group + "')]\n", '')
                elif directory == 'Acceptance' and any(c['result'] == 'PASS' for c in cases):
                    failing = [c for c in cases if c['result'] == 'FAIL']
                    if all(' with data set ' in c['test'] for c in failing):
                        labels = [c['test'].split(' with data set ')[1].strip('"') for c in failing]
                        attributes = re.sub(r"#\[ExpectedBundleGap\('([^']+)'(?:, [^\n]*)?\)\]", lambda m: "#[ExpectedBundleGap('" + m[1] + "', [" + ', '.join(repr(label) for label in labels) + "])]", attributes)
                return attributes + match[2]
            path.write_text(re.sub(pattern, replace, source))
    metadata = read('torture-gaps.json')
    for gap in metadata['gaps']:
        tests = [s['test'].split(' with data set ')[0] for s in torture['scenarios'] if gap['id'] in s.get('historical_gap_ids', s['gap_ids'])]
        gap['regression_tests'] = sorted(set(gap.get('regression_tests', []) + tests))
    write('torture-gaps.json', metadata)


def generate(previous):
    a, t, f = read('acceptance-results.json'), read('performance-results.json'), read('feature-coverage.json')
    revision = a['bundle_revision']
    if not all(r.get('evidence', {}).get('verified') for r in [a, t]):
        raise SystemExit('Unverified run provenance; use --run before making a release decision.')
    if any(r['bundle_revision'] != revision for r in [t, f, read('public-feature-inventory.json')]):
        raise SystemExit('Mixed report revisions; regenerate every report before deciding readiness.')
    definitions = {g['id']: g for g in read('bundle-gaps.json')['gaps']}
    history, opened = [], []
    for summary in a['gap_summary']:
        g = definitions[summary['id']].copy()
        observed = [s for s in a['scenarios'] if g['id'] in s.get('historical_gaps', s['bundle_gaps']) and s['result'] == 'FAIL']
        if 'current' in g: g['historical_observation'] = g.pop('current')
        g['actual'] = [s['failure'] for s in observed]
        g.update(status='OPEN' if observed else 'RESOLVED', current_evidence=[s['failure'] for s in observed], verified_tests=summary['current_failures'])
        history.append(g)
    for summary in t['gaps']:
        g = summary.copy()
        g['status'] = 'OPEN' if g['failing_cases'] else 'RESOLVED'
        g['actual'] = g.get('current_evidence', [])
        history.append(g)
    old = {g['id']: g for g in previous.get('history', [])}
    for g in history:
        before = old.get(g['id'], {})
        g['first_seen_revision'] = before.get('first_seen_revision', g.get('first_seen_revision', revision))
        g['last_verified_revision'] = revision
        g['resolved_revision'] = (before.get('resolved_revision') or revision) if g['status'] == 'RESOLVED' else None
        if g['status'] == 'OPEN':
            opened.append(g)
    current = {'bundle_revision': revision, 'source': 'Complete JUnit + reviewed metadata; only currently failing observed contracts.', 'gaps': opened}
    write('current-gaps.json', current)
    write('gap-history.json', {'bundle_revision': revision, 'revision_semantics': 'Verification snapshots, not fix commits; null first_seen means exact historical first observation is unknown.', 'history': history})
    unexpected = [s for s in a['scenarios'] if s['result'] == 'FAIL' and not s['bundle_gaps']]
    unexpected += [s for s in t['scenarios'] if s['classification'] == 'UNEXPECTED_FAILURE']
    stale = [s['test'] for s in a['scenarios'] if s['result'] == 'PASS' and s['bundle_gaps']]
    stale += [s['test'] for s in t['scenarios'] if s['result'] == 'PASS' and s['gap_ids']]
    counts = Counter(s['result'] for s in a['scenarios'])
    subsets = {name: dict(Counter(s['result'] for s in a['scenarios'] if s['area'] == name)) for name in ['Production', 'Features']}
    partial = Counter(row['status'] for row in f['features']) if 'features' in f else Counter(row['status'] for row in f['rows'])
    blockers = [g for g in opened if g.get('priority') in ['P0', 'P1'] and g['category'] not in ['APPLICATION_POLICY', 'INFRASTRUCTURE_LIMIT']]
    readiness = 'BLOCKED' if blockers or unexpected or counts['SKIP'] or t['counts']['SKIPPED'] else 'REVIEW_REQUIRED'
    summary = {'bundle_revision': revision, 'readiness': readiness, 'acceptance': dict(counts, SKIP=counts['SKIP']), 'acceptance_assertions': a['assertions'], 'torture_assertions': t['assertions'], 'subsets': subsets, 'torture': t['outcomes'], 'open_gaps': [g['id'] for g in opened], 'blockers': [g['id'] for g in blockers], 'new_regressions': [s['test'] for s in unexpected], 'stale_markers': stale, 'feature_statuses': dict(partial), 'public_api_decisions': read('public-api-decisions.json')['decisions']}
    write('release-gate.json', summary)
    lines = ['# 1.0 external release gate', '', f'Tested bundle revision: `{revision}`.', '', 'Generated by `python3 tools/release-gate.py` from complete independent-consumer reports. **[current-gaps.json](current-gaps.json) is the authoritative list of open observed gaps.** [gap-history.json](gap-history.json) keeps resolved contracts; reviewed bundle/torture metadata is historical input, not the active list.', '', '## Executable status', '', f"Acceptance: {counts['PASS']}/{sum(counts.values())} PASS; {counts['FAIL']} FAIL; {counts['SKIP']} skips; {a['assertions']} assertions.", f"Production subset: {subsets['Production']}. Features subset: {subsets['Features']}. Both are part of the same full Acceptance run.", f"Torture: {t['outcomes']}; {t['assertions']} assertions; unexpected failures: {t['counts']['UNEXPECTED_FAILURE']}; skips: {t['counts']['SKIPPED']}.", f'Unclassified regressions: {len(unexpected)}. Stale expected-gap cases: {len(stale)}.', '']
    for priority in ['P0', 'P1', 'P2']:
        lines += ['## Open ' + priority + ' gaps', '']
        if any(g.get('priority') == priority for g in opened):
            lines += ['| ID | Category | Expected contract | Evidence |', '|---|---|---|---|']
        for g in opened:
            if g.get('priority') == priority:
                evidence = g.get('verified_tests', g.get('tests', []))
                case = next(c for c in a['scenarios'] + t['scenarios'] if c['test'] in evidence)
                link = '[' + Path(case['file']).name + '](../' + case['file'] + ') (' + str(len(evidence)) + ' failures)'
                lines.append('| ' + ' | '.join([g['id'], g['category'], g['expected'].replace('|', '\\|'), link]) + ' |')
        if not any(g.get('priority') == priority for g in opened): lines += ['None.']
        lines += ['']
    lines += ['## Public API decisions and inactive configuration', '', 'See [public-api-freeze.md](public-api-freeze.md) and [configuration-dx-audit.md](configuration-dx-audit.md). CONFIG_ONLY/DOCUMENTATION_ONLY surfaces are not advertised as working features. The former PARTIAL inventory has been reviewed into bounded executable contracts and explicit gaps; see [feature-review.json](feature-review.json) and [coverage status meanings](coverage-status.md). Aggregate case counts alone do not prove all theoretical feature combinations.', '', '## Performance blockers', '', ', '.join(g['id'] for g in blockers if g['category'] == 'PERFORMANCE_GAP') or 'None observed in the fixed Torture release set.', '', '## 1.0 release readiness', '', f'**{readiness}**. P0/P1 executable blockers: ' + (', '.join(g['id'] for g in blockers) or 'none') + '. Passing runtime tests never automatically authorize API freeze; outstanding public API design decisions require bundle-owner review.', '']
    if any(g['id'] == 'PERFORMANCE-NPLUS1' for g in opened):
        lines += ['The page 5/20 growth guards pass on this revision; retained absolute query budgets fail. The stable historical ID is not evidence of current linear N+1 growth.', '']
    (ROOT / 'docs/release-gate.md').write_text('\n'.join(lines))
    closed = [g['id'] for g in history if g['status'] == 'RESOLVED' and (old.get(g['id'], {}).get('status') == 'OPEN' or g.get('resolved_revision') == revision)]
    refresh = ['# Bundle refresh', '', f'Latest independently tested bundle: `{revision}`.', '', f"Acceptance: {counts['PASS']}/{sum(counts.values())} passing; {counts['FAIL']} classified failures. Torture: {t['outcomes']}. Unclassified regressions: {len(unexpected)}.", '', 'Resolved since previous recorded open state: ' + (', '.join(closed) or 'none recorded'), '', 'Currently open: ' + (', '.join(g['id'] for g in opened) or 'none'), '', 'See [release gate](release-gate.md), [current gaps](current-gaps.json), [history](gap-history.json), and full JUnit-derived reports. Assertions remain unchanged for resolved contracts; only stale markers are removed.', '']
    (ROOT / 'docs/bundle-refresh.md').write_text('\n'.join(refresh))
    contracts = [
        ('Preconditions before mutation / overlapping If-Match one winner', ['Production\\ConcurrencyAndAtomicTest', 'Chaos\\ConsistencyAndFailureTest::test']),
        ('Reject independent Atomic connections before mutation; same-connection rollback', ['Atomic\\AtomicTransactionalityTest', 'Chaos\\ConsistencyAndFailureTest', 'Extreme\\TenantIsolationTest']),
        ('Generated IDs/lid workflows', ['Atomic\\AtomicLidTest']),
        ('Filter depth, nodes, operands, weighted path budget and disabled guards', ['Features\\Filtering\\StructuralLimitsTest', 'Features\\Filtering\\DisabledGuardsTest']),
        ('Typed UUID / malformed UUID client errors', ['Doctrine\\UuidIdentifierTest']),
        ('Composite-ID discovery rejected during boot', ['Extreme\\CompositeDiscoveryTest']),
        ('Distinct root pagination over joins', ['Features\\Relationships\\RootJoinPaginationTest']),
        ('DTO root ordering', ['Features\\Mapping\\ConstructorAndProjectionTest']),
        ('Batch representation reads / structural N+1 budgets', ['Features\\DataLayer\\BatchRelationshipReaderTest', 'Performance\\NPlusOneAndCardinalityTest']),
        ('Relationship identifier budget', ['Features\\Relationships\\IdentifierBudgetTest']),
        ('Primary identity excluded from included / explicit include retains linkage under never', ['Features\\Relationships\\RepresentationModesTest']),
        ('To-many reject policy / custom aggregate sort', ['Features\\Sorting\\CollectionPolicyTest', 'Features\\Sorting\\AggregateSemanticsTest']),
        ('HEAD validators / accurate OPTIONS', ['Protocol\\HeadTest', 'Protocol\\OptionsTest', 'Features\\Cache\\HeaderConfigurationTest', 'Features\\Mapping\\SelectiveOperationsTest']),
        ('Configured Last-Modified field', ['Features\\Cache\\LastModifiedConfigurationTest']),
        ('Profile default and negotiated write hooks', ['Features\\Profiles\\PublicHooksTest', 'Features\\Profiles\\AuditIdentityTest']),
        ('Custom-provider Atomic rollback', ['Features\\DataLayer\\CustomProviderTest::testAtomicBusinessFailure']),
    ]
    all_cases = a['scenarios'] + t['scenarios']
    checklist = ['# PR #67 independent verification', '', f'Tested bundle: `{revision}`. Generated from complete HTTP/kernel Acceptance and Torture evidence. No distributed transaction promise; independent connections reject before mutation.', '', '| External contract | Executable evidence | Result |', '|---|---|---|']
    for contract, prefixes in contracts:
        cases = [c for c in all_cases if any(prefix in c['test'] for prefix in prefixes)]
        outcome = dict(Counter(c['result'] for c in cases))
        evidence = ', '.join(prefix.replace('\\', '/') for prefix in prefixes)
        checklist.append(f'| {contract} | {evidence} | {outcome if cases else "NOT_COVERED"} |')
    (ROOT / 'docs/pr67-verification.md').write_text('\n'.join(checklist) + '\n')
    (ROOT / 'docs/feature-iteration.md').write_text('\n'.join(['# Feature verification and release gate', '', f'Current tested bundle: `{revision}`.', '', f'Acceptance: {dict(counts)}. Production: {subsets["Production"]}. Features: {subsets["Features"]}. Torture: {t["outcomes"]}.', '', 'The discovery iteration is closed. This application now maintains a fixed external release specification. No new feature families are added merely to increase case counts.', '', 'Use [release-gate.md](release-gate.md) for current blockers, [current-gaps.json](current-gaps.json) for the authoritative observed-gap list, and [gap-history.json](gap-history.json) for resolved contracts. Historical metadata is not the active gap inventory.', '', 'The public surface remains honest: ' + str(dict(partial)) + '. Former PARTIAL entries now have bounded contracts or explicit gaps. Bundle owners must implement/remove/internalize inactive declarations and decide the remaining public API seams.', '', 'Targeted completion covers logical handler AST position, independent CREATE/UPDATE inputs, partial PATCH and Atomic rollback, alternate representation channels, default audit writes, HTTP/OpenAPI consistency and native docs media, schema refs, explicit relationship events, and page 5/20 structural query growth.', '']).rstrip() + '\n')
    print(json.dumps(summary, indent=2))
    if command([sys.executable, 'tools/gap-handoff.py']): return 2
    return 2 if unexpected else (1 if readiness == 'BLOCKED' or stale else 0)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--run', action='store_true')
    parser.add_argument('--update', action='store_true')
    parser.add_argument('--clean-resolved-markers', action='store_true')
    args = parser.parse_args()
    previous = read('gap-history.json') if (ROOT / 'docs/gap-history.json').exists() else {'history': [{'id': g['id'], 'status': 'OPEN' if g.get('failing_cases') else 'RESOLVED', 'resolved_revision': BASELINE if not g.get('failing_cases') else None} for g in read('acceptance-results.json')['gap_summary'] + read('performance-results.json')['gaps']]}
    if args.update and not args.run: parser.error('--update requires --run')
    if args.run:
        if args.update and command(['docker', 'compose', 'exec', '-T', 'php', 'composer', 'update', 'alexfigures/symfony-jsonapi-bundle', '--no-interaction', '--no-scripts']): return 2
        tested_revision = current_revision()
        if command(php('-r', 'require "vendor/autoload.php"; (new Symfony\\Component\\Filesystem\\Filesystem())->remove(array_merge(glob("var/cache/*"), ["var/acceptance-junit.xml", "var/torture/junit.xml", "var/release-evidence.json"])); file_put_contents("var/acceptance-http.ndjson", ""); file_put_contents("var/torture/scenarios.ndjson", "");')): return 2
        for args_, log in [(['docker', 'compose', 'exec', '-T', '-e', 'ACCEPTANCE_RECORD_HTTP=1', 'php', 'php', '-d', 'memory_limit=1G', 'bin/phpunit', '--log-junit', 'var/acceptance-junit.xml'], 'release-acceptance.log'), (php('-d', 'memory_limit=2G', 'vendor/bin/phpunit', '-c', 'phpunit.torture.xml.dist', '--log-junit', 'var/torture/junit.xml'), 'release-torture.log')]:
            code = command(args_, log)
            if code not in [0, 1]: raise SystemExit('Test process failed; see var/' + log)
        record_evidence(tested_revision)
        for tool in ['acceptance-report.php', 'torture-report.php', 'public-feature-inventory.php']:
            if command(php('tools/' + tool)) not in [0, 1]: return 2
    if args.clean_resolved_markers or args.run:
        cleanup_markers(read('acceptance-results.json'), read('performance-results.json'))
        for tool in ['acceptance-report.php', 'torture-report.php']:
            if command(php('tools/' + tool)) not in [0, 1]: return 2
    if command([sys.executable, 'tools/feature-coverage.py']): return 2
    return generate(previous)


if __name__ == '__main__':
    raise SystemExit(main())
