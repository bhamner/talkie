# Talkie — build plan

One codebase: **Laravel + Inertia + Vue**. iOS and Android are later **Capacitor shells** around that same UI — not three feature apps. Board, auth, and catalog work ships once on the web; store builds pick it up when you compile.

## Stack

| Layer | Choice |
|---|---|
| App UI | Vue 3 + Inertia + Tailwind (shared) |
| API / auth | Laravel + Socialite (Google now; Apple later) |
| DB | SQLite locally; Postgres in production |
| TTS guests | Device voices (`speechSynthesis`) |
| TTS signed-in web | One **Piper LibriTTS** model (`en_US-libritts_r-medium`) in the browser (WASM), login required, free |
| TTS paid mobile app | Same LibriTTS model via **Sherpa-ONNX**, plus extra packs (Kokoro — hoped to include younger voices) |
| Mobile | Capacitor wrap + native TTS plugin + $10–20 paid store listing |

## Payment

| Surface | Price | Unlock |
|---|---|---|
| talkie.kids (web) | Free | Neural voices require an account. Guests keep device TTS. **No Stripe.** |
| iOS / Android | One-time **$10–20 paid app** | Buying the app is the purchase. Full mobile voice library included. No in-app voice shop. |

Do not sell digital voices on the website. Login is an account (board + settings sync, and so model downloads are not anonymous), not a paywall.

The mobile price is for the **native app + larger offline library**, not the vocabulary. A child’s board stays in Postgres on their user. They can use the free site, then open the same social login in the paid app.

**Not planned:** cloud TTS APIs (e.g. OpenAI `tts-1`), Stripe, or metered synthesis.

## TTS strategy

| Piece | Role |
|---|---|
| [Piper](https://github.com/OHF-Voice/piper1-gpl) | Shared neural voice. Web uses **only** `en_US-libritts_r-medium` (LibriTTS-R, ~79 MB, speaker 0 as Nova). Same ONNX on mobile via Sherpa. Piper’s other English packs are mostly adult single-speaker voices, so we do not ship those on the web. |
| [Kokoro](https://github.com/k2-fsa/sherpa-onnx) (via Sherpa-ONNX) | Heavier “studio” / hoped-for younger voices. **Mobile only** (too large for Safari/WASM). |
| [Sherpa-ONNX](https://github.com/k2-fsa/sherpa-onnx) | On-device inference in the Capacitor plugin (Phase 4). |

Catalog lives in `config/talkie_voices.php` with a `platforms` list (`web`, `mobile`, or both). `selectable` is computed later from platform + auth; today only `device-default` is selectable.

| Who | Voices |
|---|---|
| Guests | Friendly (`device-default`) via `speechSynthesis` |
| Signed-in web | Friendly + Nova (LibriTTS Piper) |
| Paid mobile app | Those **plus** Harbor / Spark (Kokoro and other app-only packs) |

`useSpeech` will route `provider: device` → `speechSynthesis`, `provider: bundled` → Piper WASM on web or Sherpa on Capacitor. Fall back to device TTS if a model is missing or synthesis fails. Server validation must only allow IDs selectable **on that client** (a web user cannot save Harbor).

UI copy: web-locked neural cards are “Sign in to use,” not a paywall. Mobile-only cards say “Included in the Talkie app.”

### Phase 3 — Web neural voices (when we build it)

1. Host 1–2 Piper `.onnx` + `.json` files (CDN preferred; App Platform disk is small).
2. Cache in the browser (Cache API or OPFS). First preview after login may be slow; later taps should be local.
3. Gate model fetch and bundled `selectable` on an authenticated user.
4. Extend `useSpeech` with the bundled Piper WASM path.
5. Voice catalog lock copy: login vs mobile-only.

### Phase 4 — Mobile (when we build it)

1. One Capacitor project wrapping the live app URL or a bundled web build (live URL is simpler for updates).
2. Sherpa-ONNX native plugin; reuse the **same** Piper model IDs as web; add Kokoro and extra packs.
3. Paid app on App Store Connect and Play Console ($10–20, no IAP).
4. Store listings can mention the free web board.

## Data model

- `menus` — nested folders (`parent_id`), `user_id` null = shared template
- `words` — buttons in a menu (or home when `menu_id` null); `icon` is the catalog key
- `users.preferred_name` — spoken greeting name
- `users.provider` / `provider_id` — Socialite identity
- `user_settings` — `voice_id`, `voice_uri`, `voice_name`, `onboarding_completed_at`
- Guests use the shared template; personal copy is created on signup/login

## V1 features

- [x] Schema + template seeder
- [x] Public board without login
- [x] Personalize via Socialite
- [x] Onboarding: preferred name → voice catalog
- [x] Greeting button (“Hello my name is …”)
- [x] Nested board navigation + phrase bar
- [x] Device TTS with catalog cards for future bundled voices
- [x] Customize menus/words (add/edit/delete/reorder + pronunciation)
- [x] Web Piper voice for signed-in users (LibriTTS-R medium, WASM)
- [ ] Capacitor iOS + Android paid app ($10–20) + Sherpa + larger library

## Phases

1. **Foundations** — public board, auth, onboarding, voice catalog UI — done
2. **Customization** — edit mode for menus/words (including phonetic “speak as”) — done
3. **Web neural voices** — login-gated LibriTTS Piper WASM, catalog/platform rules, download-once cache, `useSpeech` router
4. **Mobile packaging + paid store app** — Capacitor, Sherpa-ONNX, reuse web Piper models, mobile-only packs, $10–20 listing

Phase 3 is the only TTS work that belongs on the hosted site. Phase 4 does not reopen the board feature set.

## Local smoke test

```bash
php artisan migrate:fresh --seed
composer run dev
```

- Open `/board` as a guest
- Click **Personalize** → register/login (or configure Socialite keys)
- Complete name + voice onboarding
- Use the greeting button and phrase bar

Auth is SSO-only (Google now; Apple coming soon).
