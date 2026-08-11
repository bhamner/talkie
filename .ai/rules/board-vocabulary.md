# Board vocabulary: one tile, one location

Every speakable **word tile** must exist in exactly one place on the board.

## Rule

- A word `label` (case-insensitive) may appear on **one menu only** — including the home/root board (`menu_id` null).
- Do **not** duplicate the same word in another folder or section (e.g. do not put `home` in both Places and Home words).
- When adding template words in `BoardTemplateSeeder`, new user copies in `BoardTemplateService`, or icons in `boardWordIcons.ts`, check the full board first.
- If a concept fits multiple categories, pick the single best home or use a distinct label (`go home` vs root `home` is fine; two tiles both labeled `home` is not).
- Highest-frequency scored core words live on the **home board**, ordered by use frequency (`App\Support\CoreVocabulary::scoredHomeWords()`).
- Marvin / Beukelman / Bilyeu preschool words are covered via `CoreVocabulary::requiredBoardLabels()` (fillers like `um`/`ah` excluded; aliases like `ya`→`yes`, `ok`→`okay`, number words→digit tiles).
- Prefer **singular lemmas**; plurals and common endings (`s`, `ing`, `ed`, `ly`) come from grammar tiles in the phrase bar (`resources/js/lib/morphPhrase.ts` / `App\Support\MorphInflector`). Map plural study forms to singular via `preschoolLabelAliases()`.
- Built-in template words/phrases are `is_builtin`; parents **hide** them (`is_hidden`) instead of deleting. Custom (user-added) words/phrases may be deleted.

## Before adding tiles

1. Search seeders, services, and `boardWordIcons.ts` for the label.
2. Confirm no existing tile uses that label elsewhere.
3. Add or update the Pest test expectation if the vocabulary grows.

## Enforcement

`tests/Feature/BoardTemplateServiceTest.php` asserts template word labels are globally unique and that core vocabulary requirements are met. Keep that test passing when changing the board.
