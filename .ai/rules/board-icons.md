# Board icons: stored on the row, rendered from a catalog

Word and folder tiles may show a pictogram only when that tile’s `icon` column has a catalog key.

## Rule

- Assignment lives on the database row: `words.icon` and `menus.icon` (nullable string catalog keys).
- The frontend catalogs map those keys to SVG components. Do **not** look up icons by label or folder name at render time.
- A catalog glyph should still be unique to one tile when adding new assignments — same constraint as vocabulary: one tile, one location. Existing color tiles sharing `CircleIcon` are a known exception (color comes from `boardWordColor`).
- Do **not** use a generic fallback icon. If no suitable unique icon exists yet, leave `icon` null — the tile still shows its label text.
- Letter-only tiles (e.g. `a`) are allowed; that is not an icon reuse. Letter-only is still decided from the label in `boardWordIcons.ts`.
- Hugeicons catalog: `resources/js/lib/boardWordIcons.ts` (`wordIconCatalog`) and `resources/js/lib/boardFolderIcons.ts` (`folderIconCatalog`).
- Lucide keys are prefixed (`lucide:smile`) and live in `resources/js/lib/boardWordLucideIcons.ts`. Use Lucide only when explicitly requested (e.g. `I` → `lucide:smile`, `mine` → `lucide:smile-plus`, `friend` → `lucide:users-round`).
- Template seeding uses `App\Support\BoardIcons` (label/folder name → catalog key). After seed, the row is the source of truth.

## Before adding an icon

1. Add the Hugeicon (or Lucide) to the matching catalog if it is not already there.
2. Confirm no other template word or folder already uses that catalog key (except the known color-circle exception).
3. Set the key in `App\Support\BoardIcons` so `BoardTemplateSeeder` writes it onto the row.
4. If the icon is taken, leave the new tile’s `icon` null until a distinct glyph is chosen.

## UI behavior

- `BoardWordIcon.vue` renders a letter, or the Lucide/Hugeicon for `word.icon`, or nothing.
- `BoardMenuIcon.vue` renders the Hugeicon for `menu.icon`, or nothing.

## Enforcement

When changing icon assignments, grep `BoardIcons.php` and the catalogs for duplicate keys. Keep assignment aligned with `board-vocabulary.md` (one word, one menu).
