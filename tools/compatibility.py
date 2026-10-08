#!/usr/bin/env python3
"""Prepare pinned consumers and generate compatibility proof, without bypassing Composer."""
import argparse
import hashlib
import json
import os
import re
import subprocess
import shutil
import uuid
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BUNDLE = 'alexfigures/symfony-jsonapi-bundle'


def read(path):
    return json.loads((ROOT / path).read_text())


def write(path, data):
    destination = ROOT / path
    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_text(json.dumps(data, indent=2) + '\n')


def digest(path):
    return hashlib.sha256((ROOT / path).read_bytes()).hexdigest()


def contract_digest():
    """Same behavior across branches; resolved marker annotations are not assertions."""
    result = hashlib.sha256()
    for directory in ['src', 'tests']:
        for path in sorted((ROOT / directory).rglob('*.php')):
            source = path.read_text()
            source = re.sub(r"^\s*#\[(?:ExpectedBundleGap|ExpectedTortureGap)\([^\n]*\)\]\n", '', source, flags=re.M)
            source = re.sub(r"^\s*#\[Group\('(?:bundle-gap|torture-gap)'\)\]\n", '', source, flags=re.M)
            result.update(str(path.relative_to(ROOT)).encode())
            result.update(source.encode())
    return result.hexdigest()


def execute(args):
    return subprocess.run(args, cwd=ROOT, check=True)


def prepare(target, bundle, mode, output=None):
    definition = read('compatibility/targets.json')[target]
    if mode == 'release':
        bundle = bundle[1:] if bundle.startswith('v') else bundle
    if mode == 'release' and not re.fullmatch(r'1\.\d+\.\d+(?:-RC\d*)?', bundle, re.I):
        raise SystemExit('Release mode requires an exact published 1.x version, not a branch/path.')
    if mode == 'stabilization' and not re.fullmatch(r'dev-[^#]+#[0-9a-f]{40}', bundle):
        raise SystemExit('Stabilization mode requires dev-branch#exact-40-character-commit.')
    composer = read('composer.json')
    composer['minimum-stability'] = 'dev' if target == 'sf82' else 'stable'
    composer['require']['php'] = '^' + definition['php']
    # Composer validate --strict rejects commit-ref requirements. The committed
    # lock plus the explicit expected revision pin source without that warning.
    composer['require'][BUNDLE] = bundle.split('#')[0]
    composer['require']['doctrine/doctrine-bundle'] = definition['doctrine_bundle']
    composer['require']['doctrine/orm'] = definition['orm']
    composer['require']['doctrine/dbal'] = definition['dbal']
    for section in ['require', 'require-dev']:
        for name in composer[section]:
            if name.startswith('symfony/') and name not in ['symfony/flex']:
                composer[section][name] = definition['symfony']
    composer['extra']['symfony']['require'] = definition['symfony'].split('@')[0]
    composer['config']['platform'] = {'php': definition['php_platform']}
    composer['config']['bump-after-update'] = False
    if output:
        destination = Path(output)
        destination.mkdir(parents=True, exist_ok=True)
        (destination / 'composer.json').write_text(json.dumps(composer, indent=2) + '\n')
        (destination / 'platform.json').write_text(json.dumps({'target': target, 'mode': mode, 'bundle_revision': bundle.split('#')[1] if mode == 'stabilization' else None}, indent=2) + '\n')
    else:
        write('composer.json', composer)
        write('compatibility/platform.json', {'target': target, 'mode': mode, 'bundle_revision': bundle.split('#')[1] if mode == 'stabilization' else None})
    print('Prepared', definition['branch'], '; resolve on real PHP', definition['php'], 'and commit composer.lock before claiming a fixture.')


def configure_runtime():
    target = read('compatibility/platform.json')['target']
    os.environ.setdefault('COMPAT_PHP_VERSION', read('compatibility/targets.json')[target]['php'])


def activate_fixture(target):
    fixture = ROOT / 'compatibility/fixtures' / target
    platform = json.loads((fixture / 'platform.json').read_text())
    lock = json.loads((fixture / 'composer.lock').read_text())
    package = next(p for p in lock['packages'] if p['name'] == BUNDLE)
    if platform['mode'] == 'stabilization' and package['source']['reference'] != platform['bundle_revision']:
        raise SystemExit('Fixture lock disagrees with the expected bundle revision.')
    composer = json.loads((fixture / 'composer.json').read_text())
    if platform['mode'] == 'release' and package['version'].lstrip('v').lower() != composer['require'][BUNDLE].lower():
        raise SystemExit('Fixture lock does not contain the exact published release constraint.')
    for filename in ['composer.json', 'composer.lock']:
        shutil.copyfile(fixture / filename, ROOT / filename)
    shutil.copyfile(fixture / 'platform.json', ROOT / 'compatibility/platform.json')
    shutil.copyfile(fixture / 'doctrine.yaml', ROOT / 'config/packages/doctrine.yaml')
    print('Activated locked fixture:', target, '; run --clean-install next.')


def clean_install():
    """Fresh mounted directories hide, but never delete or reuse, existing vendor/cache."""
    configure_runtime()
    os.environ.setdefault('LOCAL_UID', str(os.getuid()))
    os.environ.setdefault('LOCAL_GID', str(os.getgid()))
    identifier = uuid.uuid4().hex
    os.environ['COMPAT_VENDOR_VOLUME'] = 'jsonapi-vendor-' + identifier
    os.environ['COMPAT_CACHE_VOLUME'] = 'jsonapi-cache-' + identifier
    os.environ['COMPOSE_FILE'] = str(ROOT / 'docker-compose.yml') + ':' + str(ROOT / 'compatibility/clean-install.compose.yml')
    execute(['docker', 'compose', 'up', '-d', '--wait', 'pgsql', 'mysql'])
    execute(['docker', 'compose', 'up', '-d', '--build', '--no-deps', '--force-recreate', 'php'])
    execute(['docker', 'compose', 'exec', '-T', '--user', 'root', 'php', 'sh', '-c', 'mkdir -p vendor var/cache && chown -R ' + os.environ['LOCAL_UID'] + ':' + os.environ['LOCAL_GID'] + ' vendor var'])
    execute(['docker', 'compose', 'exec', '-T', 'php', 'sh', '-c', 'test ! -e vendor/autoload.php'])
    execute(['docker', 'compose', 'exec', '-T', 'php', 'composer', 'install', '--prefer-dist', '--no-interaction'])
    execute(['docker', 'compose', 'exec', '-T', 'php', 'composer', 'check-platform-reqs'])
    for database in ['symfony_pg_test', 'symfony_pg_torture', 'symfony_pg_torture_b', 'symfony_pg_torture_replica']:
        exists = subprocess.check_output(['docker', 'compose', 'exec', '-T', 'pgsql', 'psql', '-U', 'symfony', '-d', 'symfony_pg', '-tAc', "SELECT 1 FROM pg_database WHERE datname = '" + database + "'"], cwd=ROOT, text=True).strip()
        if not exists:
            execute(['docker', 'compose', 'exec', '-T', 'pgsql', 'createdb', '-U', 'symfony', database])
    write('var/clean-install.json', {'vendor_volume': os.environ['COMPAT_VENDOR_VOLUME'], 'cache_volume': os.environ['COMPAT_CACHE_VOLUME'], 'lock_sha256': digest('composer.lock'), 'time': datetime.now(timezone.utc).isoformat()})


def capture_environment():
    code = '''require "vendor/autoload.php";
    $packages=[]; foreach (["symfony/framework-bundle", "doctrine/orm", "doctrine/dbal", "doctrine/doctrine-bundle", "alexfigures/symfony-jsonapi-bundle"] as $name) {
      $packages[$name]=["version"=>Composer\\InstalledVersions::getPrettyVersion($name),"revision"=>Composer\\InstalledVersions::getReference($name),"install_path"=>Composer\\InstalledVersions::getInstallPath($name)];
    } echo json_encode(["php"=>PHP_VERSION,"packages"=>$packages]);'''
    runtime = json.loads(subprocess.check_output(['docker', 'compose', 'exec', '-T', 'php', 'php', '-r', code], cwd=ROOT, text=True))
    runtime.update(example_revision=subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=ROOT, text=True).strip(),
                   example_dirty=bool(subprocess.check_output(['git', 'status', '--porcelain'], cwd=ROOT, text=True).strip()),
                   lock_sha256=digest('composer.lock'), composer_sha256=digest('composer.json'), contract_sha256=contract_digest(),
                   platform=read('compatibility/platform.json'), recorded_at=datetime.now(timezone.utc).isoformat())
    marker = ROOT / 'var/clean-install.json'
    runtime['clean_install'] = read('var/clean-install.json') if marker.exists() and read('var/clean-install.json')['lock_sha256'] == runtime['lock_sha256'] and read('var/clean-install.json')['vendor_volume'] == os.environ.get('COMPAT_VENDOR_VOLUME') else None
    return runtime


def resolve_fixture(target):
    """Package-level evidence on the real target PHP; no fake lock on rejection."""
    definition = read('compatibility/targets.json')[target]
    fixture = ROOT / 'compatibility/fixtures' / target
    if not (fixture / 'composer.json').exists():
        raise SystemExit('Export the target manifest with --prepare --output first.')
    image = 'jsonapi-consumer:' + definition['php']
    runtime = subprocess.check_output(['docker', 'run', '--rm', image, 'php', '-r', 'echo PHP_VERSION;'], text=True).strip()
    args = ['docker', 'run', '--rm', '-v', str(fixture) + ':/srv/app', image, 'composer', 'update', '--with-all-dependencies', '--no-install', '--no-scripts', '--no-interaction']
    result = subprocess.run(args, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
    log = ROOT / ('docs/compatibility-resolution-' + target + '.txt')
    log.write_text(result.stdout)
    findings = read('docs/compatibility-preparation.json') if (ROOT / 'docs/compatibility-preparation.json').exists() else {}
    findings[target] = {'status': 'RESOLVED_LOCK_NOT_EXECUTED' if result.returncode == 0 else 'BLOCKED_PACKAGE_RESOLUTION',
                       'php': runtime, 'symfony_constraint': definition['symfony'], 'expected_bundle_revision': read('compatibility/fixtures/' + target + '/platform.json')['bundle_revision'],
                       'composer_exit_code': result.returncode, 'command': args, 'manifest_sha256': hashlib.sha256((fixture/'composer.json').read_bytes()).hexdigest(),
                       'log': log.name, 'recorded_at': datetime.now(timezone.utc).isoformat()}
    write('docs/compatibility-preparation.json', findings)
    print(result.stdout)
    return result.returncode


def evaluate(gate, environment):
    problems = []
    platform = environment['platform']
    definition = read('compatibility/targets.json')[platform['target']]
    packages = environment['packages']
    if not environment['php'].startswith(definition['php'] + '.'):
        problems.append('Actual PHP does not match the claimed target')
    framework = packages['symfony/framework-bundle']['version'].lstrip('v')
    if not framework.startswith(definition['symfony'].split('.*')[0] + '.'):
        problems.append('Actual Symfony does not match the pinned series')
    if gate['acceptance'].get('FAIL', 0) or gate['torture'].get('FAIL', 0):
        problems.append('An executable assertion failed (known gaps also block compatibility GO)')
    if gate['new_regressions'] or gate['stale_markers'] or gate['acceptance'].get('SKIP', 0):
        problems.append('Unclassified failures, stale gap markers or skips')
    if gate['torture'].get('SKIP', 0) or any(gate.get('feature_statuses', {}).get(status, 0) for status in ['NOT_COVERED', 'PARTIAL', 'COVERED_GAP']):
        problems.append('Torture skips or unverified public feature contracts')
    if not gate['subsets']['Production'].get('PASS') or not gate['subsets']['Features'].get('PASS') or not gate['torture'].get('PASS'):
        problems.append('Required suites are incomplete')
    if environment['lock_sha256'] != digest('composer.lock') or environment['composer_sha256'] != digest('composer.json') or environment['contract_sha256'] != contract_digest():
        problems.append('Dependencies or executable contract changed after verification')
    if packages[BUNDLE]['revision'] != gate['bundle_revision']:
        problems.append('Bundle revision disagrees with full suite evidence')
    composer = read('composer.json')
    constraint = composer['require'][BUNDLE]
    lock = read('composer.lock')
    package = next(p for p in lock['packages'] if p['name'] == BUNDLE)
    if any(r.get('type') == 'path' for r in composer.get('repositories', []) if isinstance(r, dict)) or package.get('dist', {}).get('type') == 'path':
        problems.append('Local path installation is not release evidence')
    if platform['mode'] == 'release':
        if not re.fullmatch(r'1\.\d+\.\d+(?:-RC\d*)?', constraint, re.I) or package['version'].lstrip('v').lower() != constraint.lower():
            problems.append('Release mode did not install the exact published package version')
        if not environment['clean_install']:
            problems.append('Release mode requires a proven fresh package installation')
    elif not re.fullmatch(r'dev-[^#]+', constraint) or not re.fullmatch(r'[0-9a-f]{40}', platform.get('bundle_revision', '')):
        problems.append('Stabilization mode requires a branch plus an explicit exact revision and committed lock')
    else:
        if platform['bundle_revision'] != packages[BUNDLE]['revision']:
            problems.append('Pinned stabilization commit differs from installed revision')
    return problems


def report(gate):
    provenance = read('var/release-evidence.json')
    environment = provenance.get('environment')
    if not environment:
        raise SystemExit('Compatibility proof requires a new --run; historical reports lack runtime provenance.')
    problems = evaluate(gate, environment)
    evidence = dict(environment, suites={'Acceptance': gate['acceptance'], 'Production': gate['subsets']['Production'], 'Features': gate['subsets']['Features'], 'Torture': gate['torture']},
                    open_gaps=gate['open_gaps'], unexpected_failures=gate['new_regressions'], skips=gate['acceptance'].get('SKIP', 0),
                    result='NO-GO' if problems else 'GO', reasons=problems,
                    provenance=provenance['suites'], evidence_kind='STABILIZATION' if environment['platform']['mode']=='stabilization' else 'RELEASE')
    write('docs/compatibility-evidence.json', evidence)
    target = environment['platform']['target']
    write('docs/compatibility-evidence/' + target + '.json', evidence)
    lines = ['# Latest compatibility proof', '', 'Generated from complete external suite evidence. This is a platform result, not authorization to freeze the entire public API.', '',
             'Result: **' + evidence['result'] + '** (' + evidence['evidence_kind'] + ').', '',
             'Example revision: `' + environment['example_revision'] + '`; dirty source snapshot: ' + str(environment['example_dirty']) + '.',
             'Contract SHA-256: `' + environment['contract_sha256'] + '`.',
             'Bundle revision: `' + gate['bundle_revision'] + '`.', '', '| Installed dependency | Actual version |', '|---|---|', '| PHP | ' + environment['php'] + ' |']
    lines += ['| ' + k + ' | ' + v['version'] + ' |' for k,v in environment['packages'].items()]
    lines += ['', '| Suite | Result |', '|---|---|'] + ['| ' + k + ' | ' + str(v) + ' |' for k,v in evidence['suites'].items()]
    lines += ['', 'Open active gaps: ' + (', '.join(gate['open_gaps']) or 'none') + '.', '', 'Reasons: ' + ('; '.join(problems) or 'All required executable checks passed on the specified platform.')]
    lines += ['', 'Machine-readable proof: [compatibility-evidence.json](compatibility-evidence.json). Cross-platform status: [compatibility-matrix.md](compatibility-matrix.md).', '']
    (ROOT / 'docs/release-compatibility.md').write_text('\n'.join(lines))
    matrix = ['# Compatibility matrix', '', 'Only exact executed environments produce PASS. SUPPORTED requires a successful stable-platform release-mode run; development fixtures are forward evidence. Prepared targets are not compatibility claims.', '',
              '| Target branch | PHP | Symfony | Bundle | Acceptance | Torture | Status |', '|---|---|---|---|---|---|---|']
    for key, definition in read('compatibility/targets.json').items():
        path = ROOT / ('docs/compatibility-evidence/' + key + '.json')
        if path.exists():
            e = json.loads(path.read_text()); stable = 'dev' not in e['packages']['symfony/framework-bundle']['version']
            status = 'SUPPORTED' if e['result']=='GO' and e['evidence_kind']=='RELEASE' and stable else ('STABILIZATION TESTED' if stable else 'FORWARD TESTED / DEV') if e['result']=='GO' else 'UNSUPPORTED / NO-GO'
            a = str(e['suites']['Acceptance']); t = str(e['suites']['Torture']); bundle = e['packages'][BUNDLE]['version']
            matrix.append('| ['+definition['branch']+'](compatibility-evidence/'+key+'.json) | '+e['php']+' | '+e['packages']['symfony/framework-bundle']['version']+' | '+bundle+' | '+a+' | '+t+' | '+status+' |')
        else:
            preparation = read('docs/compatibility-preparation.json') if (ROOT / 'docs/compatibility-preparation.json').exists() else {}
            state = preparation.get(key, {})
            status = '[BLOCKED package resolution](' + state['log'] + ')' if state.get('status') == 'BLOCKED_PACKAGE_RESOLUTION' else 'PREPARED / no support claim'
            matrix.append('| '+definition['branch']+' | '+state.get('php',definition['php'])+' | '+definition['symfony']+' | pending | not executed | not executed | '+status+' |')
    matrix += ['', 'Canonical main policy and RC/final workflows: [compatibility-workflow.md](compatibility-workflow.md). No 8.2 stable proof is claimed by an 8.2-dev run.', '']
    (ROOT / 'docs/compatibility-matrix.md').write_text('\n'.join(matrix))
    print('Compatibility result:', evidence['result'], '; '.join(problems))
    return 1 if problems else 0


def tag(name):
    evidence = read('docs/compatibility-evidence.json')
    if evidence['result'] != 'GO' or evidence['evidence_kind'] != 'RELEASE':
        raise SystemExit('Only a verified published release/RC may receive an evidence tag.')
    if subprocess.check_output(['git', 'status', '--porcelain'], cwd=ROOT, text=True).strip():
        raise SystemExit('Commit verified source and reports before tagging; worktree must be clean.')
    expected = 'bundle-' + evidence['packages'][BUNDLE]['version'].lstrip('v').lower() + '-' + evidence['platform']['target']
    if name != expected or evidence['lock_sha256'] != digest('composer.lock') or evidence['contract_sha256'] != contract_digest():
        raise SystemExit('Tag does not match verified version/target/contract.')
    execute(['git', 'tag', '-a', name, '-m', 'Immutable external compatibility proof; see docs/compatibility-evidence.json'])


if __name__ == '__main__':
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--prepare', choices=['sf74','sf81','sf82'])
    parser.add_argument('--activate', choices=['sf74','sf81','sf82'], help='Apply a committed locked platform fixture, including its framework configuration')
    parser.add_argument('--bundle')
    parser.add_argument('--mode', choices=['stabilization','release'], default='stabilization')
    parser.add_argument('--output', help='Export dependency-only fixture manifest without changing the canonical application')
    parser.add_argument('--tag')
    parser.add_argument('--resolve', choices=['sf74','sf81','sf82'], help='Resolve an exported manifest on a built real PHP image and record packaging evidence')
    args=parser.parse_args()
    if args.prepare:
        if not args.bundle: parser.error('--prepare requires --bundle')
        prepare(args.prepare,args.bundle,args.mode,args.output)
    elif args.activate: activate_fixture(args.activate)
    elif args.tag: tag(args.tag)
    elif args.resolve: raise SystemExit(resolve_fixture(args.resolve))
    else: parser.error('Use --prepare or --tag; proof is generated by release-gate.py.')
