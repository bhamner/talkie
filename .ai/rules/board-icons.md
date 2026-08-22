# Board icons: one icon, one tile

Word and folder tiles may show a pictogram only when it is **unique** to that tile.

## Rule

- A Hugeicons (or folder) icon may be assigned to **one word label** or **one folder name** only — same constraint as vocabulary: one tile, one location.
- Do **not** reuse the same icon for two words or two folders (e.g. do not map both `I` and `my` to `UserIcon`).
- Do **not** use a generic fallback icon (no default smile, no “close enough” duplicate).
- If no suitable unique icon exists yet, **omit the icon** — the tile still shows its label text.
- Letter-only tiles (e.g. `a`) are allowed; that is not an icon reuse.
- Word icons live in `resources/js/lib/boardWordIcons.ts` (Hugeicons).
- Lucide word icons are allowed only in `resources/js/lib/boardWordLucideIcons.ts` when explicitly requested (e.g. `I` → `Smile`, `mine` → `SmilePlus`).
- Folder icons live in `resources/js/lib/boardFolderIcons.ts` (Hugeicons). Do not assign Lucide folder icons on the board unless each folder gets its own unused Lucide glyph (prefer Hugeicons for folders).

## Before adding an icon

1. Search `boardWordIcons.ts` and `boardFolderIcons.ts` for the icon import or name.
2. Confirm no other label or folder already uses that icon.
3. If the icon is taken, leave the new tile without an icon until a distinct icon is chosen.

## UI behavior

- `BoardWordIcon.vue` renders a letter, a unique Lucide or Hugeicon, or nothing.
- `BoardMenuIcon.vue` renders a unique folder Hugeicon or nothing.

## Enforcement

When changing icon maps, grep for duplicate icon identifiers across each file and remove reuse. Keep icon assignment aligned with `board-vocabulary.md` (one word, one menu).
