# Подтверждённые гэпы для реализации в бандле

Проверенная ревизия: `a17ffd40a7d3a1e642a33aaf788427bb6b117fdb`.

Источник: полный независимый прогон приложения; [current-gaps.json](current-gaps.json) содержит только актуальные наблюдаемые гэпы.

Acceptance: {'PASS': 666, 'SKIP': 0}. Torture: {'PASS': 62}. Новых неклассифицированных регрессий: 0.

Feature inventory: {'COVERED_GREEN': 330, 'CONFIG_ONLY': 17, 'NOT_COVERED': 4, 'NOT_APPLICABLE': 1}. PARTIAL — 0; ограниченные доказанные контракты описаны в [feature-review.json](feature-review.json).

## Производительность и решения перед 1.0

`PERFORMANCE-NPLUS1` закрывается только по сохранённым assertions полного прогона. Tenant-safe query-plan bridge передаёт ограничения обоих декораторов; прежний постоянный перерасход не доказывал линейного N+1. Capability и выбор provider через locator теперь явно помечены @api; публичный статус проверяется PublicSignatureTest, поведение — полным Torture-прогоном.

Свежие измерения внешнего приложения:

| Сценарий | Запросы: page 5 / page 20 | Бюджет |
|---|---|---|
| testCollectionWithoutIncludeHasBoundedQueryShape | 8 / 8 | 12 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-one" | 18 / 18 | 18 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "to-many" | 14 / 14 | 24 |
| testIncludeQueryCountDoesNotGrowWithPageSize with data set "nested" | 26 / 26 | 32 |
| testRelatedCollectionQueryCountIsBounded | 9 / 9 | 15 |

Публичные inactive surfaces требуют реализации либо удаления/депрекации до freeze; это отдельные design findings, а не дополнительные HTTP-сбои:

- **TypedRelationshipReader / TypedRelationshipUpdater**: Latest bundle documents active typed dispatch through supports(sourceType) and relationship reader/updater tags. Dedicated two-type external dispatch verification is still needed; the previous claim of no active seam is obsolete.

Намеренное ограничение: Atomic поддерживает одну транзакционную границу. Batch через независимые соединения должен отвергаться до первой мутации; распределённая транзакция не требуется.
