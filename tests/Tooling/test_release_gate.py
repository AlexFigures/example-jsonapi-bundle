"""Report safety: metadata cleanup must never rewrite executable assertions."""
import importlib.util
import json
import tempfile
import unittest
from unittest.mock import patch
from pathlib import Path

spec = importlib.util.spec_from_file_location('gate', Path(__file__).parents[2] / 'tools/release-gate.py')
gate = importlib.util.module_from_spec(spec)
spec.loader.exec_module(gate)


class CleanupTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.previous_root = gate.ROOT
        gate.ROOT = Path(self.temp.name)
        (gate.ROOT / 'docs/history').mkdir(parents=True)
        (gate.ROOT / 'docs/history/torture-gaps.json').write_text('{"gaps": []}')
        self.path = gate.ROOT / 'tests/Acceptance/DemoTest.php'
        self.path.parent.mkdir(parents=True)

    def tearDown(self):
        gate.ROOT = self.previous_root
        self.temp.cleanup()

    def run_cleanup(self, cases):
        gate.cleanup_markers({'scenarios': cases}, {'scenarios': []})
        return self.path.read_text()

    def test_resolved_marker_removed_and_assertion_unchanged(self):
        self.path.write_text("    #[Group('bundle-gap')]\n    #[ExpectedBundleGap('GAP')]\n    public function testRule(): void\n    {\n        self::assertSame(200, $status);\n    }\n")
        source = self.run_cleanup([{'test': 'App\\Tests\\Acceptance\\DemoTest::testRule', 'result': 'PASS'}])
        self.assertNotIn('ExpectedBundleGap', source)
        self.assertNotIn('bundle-gap', source)
        self.assertIn('self::assertSame(200, $status);', source)

    def test_mixed_provider_keeps_only_actual_failure_marker(self):
        self.path.write_text("    #[DataProvider('cases')]\n    #[Group('bundle-gap')]\n    #[ExpectedBundleGap('GAP')]\n    public function testRule(string $channel): void\n    {\n        self::assertSame(200, $status);\n    }\n")
        source = self.run_cleanup([{'test': 'App\\Tests\\Acceptance\\DemoTest::testRule with data set "item"', 'result': 'FAIL'}, {'test': 'App\\Tests\\Acceptance\\DemoTest::testRule with data set "sparse"', 'result': 'PASS'}])
        self.assertIn("ExpectedBundleGap('GAP', ['item'])", source)
        self.assertIn("DataProvider('cases')", source)
        self.assertIn('self::assertSame(200, $status);', source)


class AttemptEvidenceTest(unittest.TestCase):
    def test_install_failure_has_no_go_marker_even_with_old_green_evidence(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / 'var').mkdir()
            (root / 'var/release-evidence.json').write_text('{"result":"GO"}')
            with patch.object(gate, 'ROOT', root), patch.object(gate.sys, 'argv', ['release-gate.py', '--run']), patch.object(gate, 'main', side_effect=RuntimeError('Composer install failed')):
                with self.assertRaises(RuntimeError):
                    gate.cli()
            marker = json.loads((root / 'var/release-run.json').read_text())
            self.assertEqual('NO-GO', marker['result'])
            self.assertFalse(marker['complete_evidence'])
            self.assertIn('Composer install failed', marker['error'])


if __name__ == '__main__':
    unittest.main()
