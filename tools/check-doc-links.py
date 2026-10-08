#!/usr/bin/env python3
"""Check repository-relative Markdown links in the active documentation path."""
import re
from pathlib import Path

root = Path(__file__).resolve().parent.parent
documents = [root / name for name in ['README.md', 'ACCEPTANCE.md', 'TORTURE.md']]
documents += list((root / 'docs').glob('*.md'))
missing = []
for document in documents:
    for target in re.findall(r'\]\(([^)]+)\)', document.read_text()):
        if '://' in target or target.startswith('#'):
            continue
        destination = target.split('#')[0].strip('<>')
        if not (document.parent / destination).exists():
            missing.append(f'{document.relative_to(root)}: {target}')
if missing:
    raise SystemExit('\n'.join(missing))
print(f'Local links OK in {len(documents)} active documents.')
