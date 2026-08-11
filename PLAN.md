# Talkie — build plan

One codebase: **Laravel + Inertia + Vue** (web now), wrap with **Capacitor** later for iOS and Google Play.

## Stack

| Layer | Choice |
|---|---|
| App UI | Vue 3 + Inertia + Tailwind |
| API / auth | Laravel + Socialite (Google, Apple, Facebook) |
| DB | SQLite locally; MySQL fine in MAMP/production |
| TTS free | Device voices (`speechSynthesis`) |
| TTS premium | **Downloadable on-device neural voices** (Piper + Kokoro via Sherpa-ONNX) |
| Mobile | Capacitor wrapping the same UI + native TTS plugin |

## TTS strategy

**Free tier:** browser/OS `speechSynthesis` (what ships today).

**Premium tier:** realistic voices that **download into the app** and synthesize **locally** — no per-character API billing, works offline after download.

| Piece | Role |
|---|---|
| [Piper](https://github.com/OHF-Voice/piper1-gpl) | Fast neural voices (~25–60 MB ONNX models); good default premium option |
| [Kokoro](https://github.com/k2-fsa/sherpa-onnx) (via Sherpa-ONNX) | Higher-quality neural voices (~80–360 MB); premium “studio” tier |
| [Sherpa-ONNX](https://github.com/k2-fsa/sherpa-onnx) | On-device inference engine for Capacitor (iOS/Android native plugin) |

**Not planned:** cloud TTS APIs (e.g. OpenAI `tts-1`). Tap-heavy AAC usage fits bundled models + optional in-app purchase to unlock voice packs, not metered synthesis.

### Premium implementation (Phase 4)

1. Capacitor shell + Sherpa-ONNX native plugin (synthesize to audio buffer / WAV).
2. Voice packs as downloadable assets (bundled in app or fetched once on unlock); catalog entries in `config/talkie_voices.php` map to model IDs.
3. Extend `useSpeech` (or sibling composable) to route: `provider: device` → `speechSynthesis`, `provider: bundled` → native Piper/Kokoro.
4. Web-only users keep device voices; premium unlock messaging on mobile (or optional WASM later — not v1 premium path).
5. Fallback to device TTS if model missing or synthesis fails.

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
- [x] Customize menus/words (add/edit/delete/reorder + pronunciation)
- [ ] Capacitor iOS + Android shells
- [ ] Premium bundled voices (Piper/Kokoro download + unlock catalog cards)

## Phases

1. **Foundations** — public board, auth, onboarding, voice catalog UI
2. **Customization** — edit mode for menus/words (including phonetic “speak as”)
3. **Mobile packaging** — Capacitor, store test builds, Sherpa-ONNX plugin scaffold
4. **Premium voices** — downloadable Piper/Kokoro models, native synthesis, device fallback

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
