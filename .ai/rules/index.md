# Project rules index

Map of globs → rule files. Agents must read every matching rule before editing covered paths, and grep `.ai/rules` for related keywords.

| Globs | Rule |
| --- | --- |
| `*` (always) | [catch-linter-errors.md](./catch-linter-errors.md) |
| `database/seeders/BoardTemplateSeeder.php`, `app/Services/BoardTemplateService.php`, `app/Models/Word.php`, `app/Models/Menu.php`, `app/Support/CoreVocabulary.php`, `app/Support/BoardIcons.php`, `app/Support/MorphInflector.php`, `resources/js/lib/boardWordIcons.ts`, `resources/js/lib/boardWordLucideIcons.ts`, `resources/js/lib/boardFolderIcons.ts`, `resources/js/lib/morphPhrase.ts`, `resources/js/components/BoardWordIcon.vue`, `resources/js/components/BoardMenuIcon.vue`, `tests/Feature/Board*Test.php` | [board-vocabulary.md](./board-vocabulary.md), [board-icons.md](./board-icons.md) |
| `tailwind.config.*`, `vite.config.*`, `postcss.config.*`, `eslint.config.*` | [esm-js-configs.md](./esm-js-configs.md) |
