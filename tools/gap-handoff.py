#!/usr/bin/env python3
"""Produce a bundle implementation handoff from the authoritative release evidence."""
import json
from pathlib import Path

root = Path(__file__).resolve().parent.parent
current = json.loads((root / 'docs/current-gaps.json').read_text())
gate = json.loads((root / 'docs/release-gate.json').read_text())
features = json.loads((root / 'docs/feature-coverage.json').read_text())
lines = ['# Подтверждённые гэпы для реализации в бандле', '',
         f"Проверенная ревизия: `{current['bundle_revision']}`.", '',
         'Источник: полный независимый прогон приложения; [current-gaps.json](current-gaps.json) содержит только актуальные наблюдаемые гэпы.', '',
         f"Acceptance: {gate['acceptance']}. Torture: {gate['torture']}. Новых неклассифицированных регрессий: {len(gate['new_regressions'])}.", '',
         f"Feature inventory: {features['counts']}. PARTIAL — {features['counts'].get('PARTIAL', 0)}; ограниченные доказанные контракты описаны в [feature-review.json](feature-review.json).", '']
for g in sorted(current['gaps'], key=lambda x: (x['priority'], x['id'])):
    lines += [f"## {g['id']} — {g['priority']} / {g['category']}", '',
              '**Ожидается:** ' + g['expected'], '',
              '**Наблюдается:** ' + g.get('historical_observation', g.get('current', 'См. точные assertion failures в current-gaps.json.')), '',
              '**Внешний контракт для бандла:** ' + g.get('bundle_change_required', g.get('why_bundle', g['expected'])), '']
    tests = g.get('verified_tests', g.get('tests', g.get('regression_tests', [])))
    if tests:
        lines += ['**Падающие проверки:**', '']
        lines += ['- `' + (t['test'] if isinstance(t, dict) else t) + '`' for t in tests]
        lines += ['']
lines += ['## Производительность и решения перед 1.0', '',
          '`PERFORMANCE-NPLUS1` закрывается только по сохранённым assertions полного прогона. Tenant-safe query-plan bridge передаёт ограничения обоих декораторов; прежний постоянный перерасход не доказывал линейного N+1. Capability и выбор provider через locator теперь явно помечены @api; публичный статус проверяется PublicSignatureTest, поведение — полным Torture-прогоном.', '',
          'Свежие измерения внешнего приложения:', '',
          '| Сценарий | Запросы: page 5 / page 20 | Бюджет |', '|---|---|---|']
performance = json.loads((root / 'docs/performance-results.json').read_text())
for s in performance['scenarios']:
    if 'NPlusOneAndCardinalityTest' not in s['test'] or 'Sparse' in s['test']: continue
    name = s['test'].split('::')[1]
    budget = 12 if 'WithoutInclude' in name else 15 if 'RelatedCollection' in name else 18 if 'to-one' in name else 24 if 'to-many' in name else 32
    lines.append('| '+name+' | '+' / '.join(str(m['query_count']) for m in s['http'])+' | '+str(budget)+' |')
lines += ['', 'Публичные inactive surfaces требуют реализации либо удаления/депрекации до freeze; это отдельные design findings, а не дополнительные HTTP-сбои:', '']
for d in gate['public_api_decisions']:
    if d['status'] != 'DESIGN_DECISION': continue
    lines.append('- **' + d['surface'] + '**: ' + d['direction'])
lines += ['', 'Намеренное ограничение: Atomic поддерживает одну транзакционную границу. Batch через независимые соединения должен отвергаться до первой мутации; распределённая транзакция не требуется.', '']
(root / 'docs/bundle-implementation-gaps.md').write_text('\n'.join(lines))
