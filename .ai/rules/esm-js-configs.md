# ESM JS configs

`package.json` sets `"type": "module"`.

- Root configs that use `import`/`export` (`.js` / `.mjs` / `.ts`) must stay ESM end-to-end — no bare `require()`.
- Prefer `*.cjs` + `require()` for Tailwind/PostCSS configs that depend on CommonJS-only packages (`export =` typings), e.g. `tailwind.config.cjs`.
- Under NodeNext, ESM imports of subpaths may need the `.js` extension (`tailwindcss/defaultTheme.js`).

```js
// Bad: ESM file with require()
plugins: [require('tailwindcss-animate')],

// Good: dedicated CommonJS config
// tailwind.config.cjs
const animate = require('tailwindcss-animate');
module.exports = { plugins: [animate] };
```
