# Talkie — build plan

One codebase: **Laravel + Inertia + Vue** (web now), wrap with **Capacitor** later for iOS and Google Play.

## Stack

| Layer | Choice |
|---|---|
| App UI | Vue 3 + Inertia + Tailwind |
| API / auth | Laravel + Socialite (Google, Apple, Facebook) |
| DB | SQLite locally; MySQL fine in MAMP/production |
| TTS v1 | Device voices (`speechSynthesis`) behind a premium-styled catalog UI |
| TTS later | Cloud premium voices unlock in the same catalog |
| Mobile | Capacitor wrapping the same UI |

## Data model

- `menus` — nested folders (`parent_id`), `user_id` null = shared template
- `words` — buttons in a menu (or home when `menu_id` null)
- `users.preferred_name` — spoken greeting name
- `users.provider` / `provider_id` — Socialite identity
- `user_settings` — `voice_id`, `voice_uri`, `voice_name`, `onboarding_completed_at`
- Guests use the shared template; personal copy is created on signup/login

## V1 features

- [x] Schema + template seeder
- [x] Public board without login
- [x] Personalize via Socialite + email auth
- [x] Onboarding: preferred name → voice catalog
- [x] Greeting button (“Hello my name is …”)
- [x] Nested board navigation + phrase bar
- [x] Device TTS with premium-styled locked voice cards
- [ ] Customize menus/words (add/edit/delete/reorder)
- [ ] Capacitor iOS + Android shells
- [ ] Premium/cloud voices (unlock catalog cards)

## Phases

1. **Foundations** — public board, auth, onboarding, voice catalog UI
2. **Customization** — edit mode for menus/words
3. **Mobile packaging** — Capacitor, store test builds
4. **Premium voices** — cloud TTS provider + fallback to device

## Local smoke test

```bash
php artisan migrate:fresh --seed
composer run dev
```

- Open `/board` as a guest
- Click **Personalize** → register/login (or configure Socialite keys)
- Complete name + voice onboarding
- Use the greeting button and phrase bar

Seeded user: `test@example.com` (SSO-style factory user; onboarding already complete). Auth is SSO-only.
