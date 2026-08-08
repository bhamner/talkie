# Project rules index

Map of globs → rule files. Agents must read every matching rule before editing covered paths, and grep `.ai/rules` for related keywords.

| Globs | Rule |
| --- | --- |
| `*` (always) | [catch-linter-errors.md](./catch-linter-errors.md) |
| `tailwind.config.*`, `vite.config.*`, `postcss.config.*`, `eslint.config.*` | [esm-js-configs.md](./esm-js-configs.md) |
