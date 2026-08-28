# Talkie

Talkie is an open-source children’s speech communication board. Tap word and folder buttons to build phrases and speak them aloud. Guests can use a shared starter board; signing in lets each person save a preferred name, voice, and personalized board.

Built with **Laravel 12**, **Inertia**, **Vue 3**, **Tailwind CSS**, and device text-to-speech. Social login is supported via Laravel Socialite (Google now; Apple later). Android is a Capacitor shell around [https://talkie.kids](https://talkie.kids) with native Piper via Sherpa-ONNX.

## Features

- Public word board (no login required)
- Nested word menus and phrase builder
- Personalize flow: social or email auth → name → voice catalog
- One-tap greeting: “Hello, my name is …”
- Device TTS for guests; signed-in web uses LibriTTS Piper (Nova); Kokoro and extra voices are for the mobile app

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 20+ and npm
- SQLite (default) or MySQL/MariaDB
- Android Studio + JDK 17+ (only if you are building the Play app)

## Quick start (developers)

```bash
# 1. Clone
git clone https://github.com/bhamner/talkie.git
cd talkie

# 2. PHP dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Database (SQLite by default)
touch database/database.sqlite
php artisan migrate --seed

# 5. Frontend
npm install
npm run build
# or for hot reload while developing: npm run dev

# 6. Run the app
composer run dev
```

Then open [http://127.0.0.1:8000](http://127.0.0.1:8000) (or the URL shown by `php artisan serve`).

`composer run dev` starts the Laravel server, queue worker, log viewer, and Vite together.

### Seeded test user

After `php artisan migrate --seed`, a local SSO-style user exists (`test@example.com`) with onboarding completed and a personal board copy. Sign-in in the app is single sign-on only (Google now; Apple later) — Talkie does not store passwords.

### Social login

Auth uses OAuth only. Add credentials from each provider console to `.env` (see `.env.example`):

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

APPLE_CLIENT_ID=
APPLE_CLIENT_SECRET=
APPLE_REDIRECT_URI="${APP_URL}/auth/apple/callback"
APPLE_TEAM_ID=
APPLE_KEY_ID=
APPLE_PRIVATE_KEY=
```

Callback URLs must match your `APP_URL`. The Android app (`kids.talkie`) still uses that HTTPS callback, then hands the session back into the WebView. See [Android Play listing](docs/android-play.md) for signing, SHA-1, and the `.aab` steps.

### Useful commands

```bash
# Run tests
php artisan test

# Capacitor Android app
npm run cap:sync
npm run cap:android

# Format PHP
./vendor/bin/pint

# Lint / format frontend
npm run lint
npm run format

# Fresh database
php artisan migrate:fresh --seed
```

### Laravel Boost (AI-assisted development)

This project includes [Laravel Boost](https://laravel.com/docs/boost) as a **dev dependency**. After `composer install`, generate local agent guidelines and MCP config:

```bash
php artisan boost:install
```

Boost files such as `boost.json`, `AGENTS.md`, and Cursor MCP/skills config are gitignored and regenerated per machine. Periodically refresh them with:

```bash
php artisan boost:update
```

## Project structure (high level)

| Path | Purpose |
| ---- | ------- |
| `app/Http/Controllers` | Board, auth, onboarding, settings |
| `app/Services/BoardTemplateService.php` | Copies shared template board to a user |
| `config/talkie_voices.php` | Voice catalog (device TTS + LibriTTS Piper) |
| `android/` | Capacitor Android project (`kids.talkie`) |
| `plugins/sherpa-tts` | Native Piper TTS (Sherpa-ONNX) |
| `docs/android-play.md` | Signing, OAuth SHA-1, and Play `.aab` steps |
| `database/seeders/BoardTemplateSeeder.php` | Shared starter words/menus |
| `resources/js/pages/board` | Full-screen speaking board UI |
| `resources/js/pages/onboarding` | Name + voice onboarding |
| `PLAN.md` | Product roadmap notes |

## Contributing

Thanks for helping improve Talkie. Contributions of all sizes are welcome—bug fixes, accessibility improvements, new starter words, docs, and tests.

### How to contribute

1. **Fork** the repository and create a branch from `main`:
   ```bash
   git checkout -b feature/short-description
   ```
2. **Install** and run the app locally (see [Quick start](#quick-start-developers)).
3. Make focused changes that match existing style (Laravel Pint for PHP, Prettier/ESLint for Vue/TS).
4. Add or update tests when behavior changes.
5. Run the suite before opening a PR:
   ```bash
   php artisan test
   ./vendor/bin/pint --dirty
   npm run lint
   ```
6. Open a **pull request** against `main` with a clear description of *what* changed and *why*.

### Contribution guidelines

- Prefer small, reviewable PRs over large mixed changes.
- Do not commit `.env`, API keys, or personal OAuth credentials.
- Keep the guest board usable without login.
- UI should stay kid-friendly: bright, high-contrast, large tap targets.
- New words/menus for the shared template belong in `BoardTemplateSeeder` (and related tests if needed).
- Be respectful in issues and reviews; assume good intent.

### Reporting bugs

Open a GitHub issue with:

- Steps to reproduce
- Expected vs actual behavior
- PHP / Node / browser versions when relevant
- Screenshots or console errors if UI-related

### Feature ideas

Discuss larger features in an issue first so we can align on scope (especially premium voice packs, in-app purchases, or mobile store packaging).

## License

Talkie is open source under the [MIT License](LICENSE).
