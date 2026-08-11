# Board vocabulary: one tile, one location

Every speakable **word tile** must exist in exactly one place on the board.

## Rule

- A word `label` (case-insensitive) may appear on **one menu only** — including the home/root board (`menu_id` null).
- Do **not** duplicate the same word in another folder or section (e.g. do not put `home` in both Places and Home words).
- When adding template words in `BoardTemplateSeeder`, new user copies in `BoardTemplateService`, or icons in `boardWordIcons.ts`, check the full board first.
- If a concept fits multiple categories, pick the single best home or use a distinct label (`go home` vs root `home` is fine; two tiles both labeled `home` is not).

## Before adding tiles

1. Search seeders, services, and `boardWordIcons.ts` for the label.
2. Confirm no existing tile uses that label elsewhere.
3. Add or update the Pest test expectation if the vocabulary grows.

## Enforcement

`tests/Feature/BoardTemplateServiceTest.php` asserts template word labels are globally unique. Keep that test passing when changing the board.
