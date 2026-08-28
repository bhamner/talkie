# Android Play listing

Talkie on Google Play is a **paid** Capacitor shell (`kids.talkie`) that loads [https://www.talkie.kids](https://www.talkie.kids) and speaks Piper through native Sherpa-ONNX. Do not upload a build until Piper plays on a device or emulator through Sherpa (not browser WASM).

## Local run

You need **Android Studio**, **JDK 17+**, and an Android 14+ emulator or phone.

```bash
npm install
npx cap sync android
npx cap open android
```

In Android Studio: Run **app** on a device (not a Gradle task like `updateDaemonJvm`). First Piper use downloads ~80 MB of model files into app storage.

Emulator WebView can fail to load the site even when Chrome on the same AVD works. The apex `https://talkie.kids` is a Namecheap forward whose HTTPS port times out; the live app is `https://www.talkie.kids`. Chrome also uses its own DNS; the WebView logs `Failed to read DnsConfig`. Talkie loads `www` through Google DNS at `8.8.8.8` so that emulator bug does not matter. A physical phone is still the better Play-store check.

## Signing

1. In Android Studio, **Build → Generate Signed App Bundle**. Create an upload keystore (store it off git; `*.keystore` is gitignored).
2. Enable **Play App Signing** when you create the Play listing. Google holds the app-signing key; you keep the upload key.
3. Build the release bundle: `android/app/build/outputs/bundle/release/app-release.aab` (or **Build → Generate Signed App Bundle**).

## Google OAuth (SHA-1)

The website already uses a **web** OAuth client. Android also needs an **Android** client ID in Google Cloud for `kids.talkie`.

Use the **app signing** certificate SHA-1 from Play Console (**Setup → App signing**), not only the upload key:

```bash
keytool -list -v -keystore /path/to/upload.keystore
```

Debug builds use the Android debug keystore SHA-1 instead.

Add that SHA-1 plus package name `kids.talkie` to the Android OAuth client. Keep `GOOGLE_REDIRECT_URI` as `https://talkie.kids/auth/google/callback`. Google login opens Chrome Custom Tabs, then returns to the app via `talkie://auth`.

## Play Console

- Play developer account (~$25 one-time) and a **payments profile** (required for a paid app)
- Price: $10–20 as a paid listing (no in-app voice shop)
- Store listing: icon, screenshots, short/full description, privacy policy / terms on talkie.kids (`/privacy`, `/terms`)
- Content rating questionnaire
- Upload the `.aab` to a testing track, then production after Sherpa TTS works on a real device
