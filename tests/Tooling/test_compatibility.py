"""A green report must never certify the wrong runtime or a failing known gap."""
import copy
import importlib.util
import json
import tempfile
import unittest
from pathlib import Path

spec = importlib.util.spec_from_file_location('compatibility', Path(__file__).parents[2] / 'tools/compatibility.py')
compat = importlib.util.module_from_spec(spec)
spec.loader.exec_module(compat)


class CompatibilityProofTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.previous_root = compat.ROOT
        compat.ROOT = Path(self.temp.name)
        (compat.ROOT / 'compatibility').mkdir()
        (compat.ROOT / 'composer.json').write_text(json.dumps({'require': {compat.BUNDLE: 'dev-release'}}))
        (compat.ROOT / 'composer.lock').write_text(json.dumps({'packages': [{'name': compat.BUNDLE, 'version': 'dev-release', 'dist': {'type': 'zip'}}]}))
        (compat.ROOT / 'compatibility/targets.json').write_text(json.dumps({'sf74': {'php': '8.2', 'symfony': '7.4.*'}}))
        self.gate = {'bundle_revision': 'a'*40, 'acceptance': {'PASS': 1}, 'torture': {'PASS': 1}, 'new_regressions': [], 'stale_markers': [], 'subsets': {'Production': {'PASS': 1}, 'Features': {'PASS': 1}}}
        self.environment = {'php': '8.2.34', 'platform': {'target': 'sf74', 'mode': 'stabilization', 'bundle_revision': 'a'*40}, 'packages': {'symfony/framework-bundle': {'version': 'v7.4.20'}, compat.BUNDLE: {'revision': 'a'*40}}, 'lock_sha256': compat.digest('composer.lock'), 'composer_sha256': compat.digest('composer.json'), 'contract_sha256': compat.contract_digest(), 'clean_install': None}

    def tearDown(self):
        compat.ROOT = self.previous_root
        self.temp.cleanup()

    def test_matching_stabilization_snapshot_can_be_green(self):
        self.assertEqual([], compat.evaluate(self.gate, self.environment))

    def test_wrong_php_cannot_certify_target(self):
        self.environment['php'] = '8.4.26'
        self.assertTrue(compat.evaluate(self.gate, self.environment))

    def test_known_failure_is_no_go_even_without_unclassified_regressions(self):
        self.gate['acceptance']['FAIL'] = 1
        self.assertTrue(compat.evaluate(self.gate, self.environment))

    def test_unverified_public_surface_prevents_compatibility_go(self):
        self.gate['feature_statuses'] = {'COVERED_GREEN': 332, 'NOT_COVERED': 4}
        self.assertTrue(compat.evaluate(self.gate, self.environment))

    def test_torture_skip_cannot_certify_a_platform(self):
        self.gate['torture']['SKIP'] = 1
        self.assertTrue(compat.evaluate(self.gate, self.environment))

    def test_source_or_lock_drift_rejects_old_evidence(self):
        (compat.ROOT / 'composer.lock').write_text('{}')
        # Restore valid structure with a different version; the digest must still reject it.
        (compat.ROOT / 'composer.lock').write_text(json.dumps({'packages': [{'name': compat.BUNDLE, 'version': 'dev-other'}]}))
        self.assertTrue(compat.evaluate(self.gate, self.environment))

    def test_published_release_requires_fresh_install(self):
        composer = {'require': {compat.BUNDLE: '1.0.0-RC1'}}
        (compat.ROOT / 'composer.json').write_text(json.dumps(composer))
        (compat.ROOT / 'composer.lock').write_text(json.dumps({'packages': [{'name': compat.BUNDLE, 'version': '1.0.0-RC1'}]}))
        self.environment.update(platform={'target': 'sf74', 'mode': 'release'}, lock_sha256=compat.digest('composer.lock'), composer_sha256=compat.digest('composer.json'))
        self.assertTrue(compat.evaluate(self.gate, self.environment))
        self.environment['clean_install'] = {'vendor_volume': 'fresh'}
        self.assertEqual([], compat.evaluate(self.gate, self.environment))
