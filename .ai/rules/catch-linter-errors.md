# Catch linter errors before finishing

After creating or editing any file, verify lint cleanliness before reporting done. Do not leave lint errors for the user to find in CI or the IDE.

1. Edit files.
2. Check IDE diagnostics on every touched path.
3. If any JS/TS/Vue files changed, run `npm run lint` and fix all issues.
4. If any PHP files changed, run `vendor/bin/pint --dirty --format agent`.
5. Re-check until clean, then summarize.
