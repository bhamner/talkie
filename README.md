# Talkie

Talkie is an open-source augmentative and alternative communication (AAC) application designed to help children communicate through a simple, interactive speech board.

Children can select words, navigate categories, build phrases, and have those phrases spoken aloud. The application is designed to be approachable, with large buttons, simple navigation, and minimal distractions.

Guests can use a shared starter board without creating an account, while signed-in users can personalize their board, choose a voice, and save their preferences.

## Project Status

**Talkie is currently not in active development.**

The application was previously hosted at talkie.kids, but I decided to discontinue hosting due to the ongoing costs of maintaining the service.

I also explored distributing Talkie as a mobile application using Capacitor, but licensing and app store distribution requirements made that less practical than I originally anticipated.

The source code remains available for anyone interested in running the application locally, exploring its implementation, or building something similar.

## Why Talkie?

The idea behind Talkie was to make a straightforward communication tool that children could start using without having to navigate complicated menus or go through a lengthy setup process.

I wanted the basic experience to be available without an account. Personalization would be there for those who needed it, but it wouldn't get in the way of someone simply wanting to communicate.

I was also interested in exploring how a web application could provide a consistent experience across browsers and mobile devices while taking advantage of native speech synthesis where available.

## Features

- **Guest access** — Use the shared communication board without creating an account.
- **Phrase builder** — Select words and combine them into spoken phrases.
- **Nested word categories** — Organize vocabulary into folders that children can navigate.
- **Personalized boards** — Give signed-in users their own copy of the communication board.
- **Voice selection** — Choose from available speech synthesis voices.
- **Personalized greetings** — Introduce yourself with a single button.
- **Social authentication** — Sign in using OAuth without maintaining a separate password.
- **Mobile integration** — Android application shell built with Capacitor.
- **Native speech synthesis** — Mobile text-to-speech integration using Piper and Sherpa-ONNX.

## Technology Stack

| Technology | Purpose |
| --- | --- |
| Laravel 12 | Backend and application logic |
| Inertia.js | Integration between Laravel and Vue |
| Vue 3 | Interactive frontend |
| TypeScript | Frontend development |
| Tailwind CSS | Interface styling |
| Laravel Socialite | Social authentication |
| SQLite / MySQL / MariaDB | Data storage |
| Capacitor | Mobile application integration |
| Piper / Sherpa-ONNX | Neural speech in the browser, and on Android through Sherpa-ONNX |

## How It Works

### Communication Board

The communication board is the main interface of the application.

Words are displayed as buttons and can be organized into nested categories. Selecting a word adds it to the current phrase, which can then be spoken aloud.

The guest board is populated from a shared vocabulary template, so someone can begin using the application immediately.

### Personalization

Users who sign in go through a short onboarding process where they enter their preferred name and select a voice.

The application creates a personal copy of the starter board rather than modifying the shared template. This keeps each user's vocabulary and preferences separate.

### Speech Synthesis

One of the more interesting parts of this project was working with different text-to-speech implementations.

In the browser, guests use the device's built-in speech synthesis. After sign-in, the voice catalog also offers Piper, a LibriTTS voice (`en_US-libritts_r-medium`) that runs in the browser. On Android, that same Piper voice is spoken through Sherpa-ONNX, including for guests, who hear it by default. Web and mobile share this two-voice catalog: the device voice and Piper.

The voice catalog is maintained separately from the communication board, allowing voice configurations to change without changing how the board itself works.

### Mobile Application

Talkie includes an Android project built with Capacitor.

The idea was to reuse the existing web interface while adding native text-to-speech capabilities through a custom Capacitor plugin.

The Android application was originally configured to load the hosted version of Talkie. Since that service is no longer available, the mobile configuration will need to point to a self-hosted instance before it can be used.

The repository includes documentation covering Android signing, OAuth configuration, and application bundle generation.

## Requirements

- PHP 8.3
- Composer 2
- Node.js 20
- npm 10 or newer
- SQLite, MySQL, or MariaDB

For Android development, you will also need Android Studio and JDK 21.

## Running Locally

Clone the repository:

```bash
git clone https://github.com/bhamner/talkie.git
cd talkie
```

Install the PHP dependencies:

```bash
composer install
```

Create the environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

Talkie uses SQLite by default, making it easy to get started without configuring a separate database server.

```bash
touch database/database.sqlite
php artisan migrate --seed
```

Install the frontend dependencies and build the assets:

```bash
npm install
npm run build
```

Start the development environment:

```bash
composer run dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000), or the URL displayed in your terminal.

The development command starts the Laravel server, queue worker, log viewer, and Vite development server together.

You can use the guest communication board without configuring social authentication. `php artisan migrate --seed` loads that shared board and does not create a user account. Talkie has no password login; signed-in use requires Google OAuth, described below.

## Social Authentication

Talkie uses Laravel Socialite for OAuth authentication.

Google authentication is implemented, and the project includes configuration for planned Apple authentication support.

To configure Google authentication, create OAuth credentials through the Google developer console and add them to your `.env` file.

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

The repository also includes the following Apple authentication configuration:

```env
APPLE_CLIENT_ID=
APPLE_CLIENT_SECRET=
APPLE_REDIRECT_URI="${APP_URL}/auth/apple/callback"
APPLE_TEAM_ID=
APPLE_KEY_ID=
APPLE_PRIVATE_KEY=
```

Make sure the callback URLs match your application's configured URL and the URLs registered with the authentication provider.

The Android implementation uses an HTTPS OAuth callback before returning the authenticated session to the application's WebView.

Additional mobile authentication setup is documented in [Android Play listing](docs/android-play.md).

## Android Development

The Android application is located in the `android/` directory.

Before building the application, update its configuration to use your own hosted Talkie instance.

Synchronize the Capacitor project:

```bash
npm run cap:sync
```

Open the project in Android Studio:

```bash
npm run cap:android
```

The native speech synthesis implementation is located in `plugins/sherpa-tts`.

See [Android Play listing](docs/android-play.md) for information about application signing, OAuth SHA-1 fingerprints, and generating an Android App Bundle.

The distribution documentation reflects the original development environment and may need to be updated for current Google Play requirements.

## Project Structure

| Path | Purpose |
| --- | --- |
| `app/Http/Controllers` | Communication board, authentication, onboarding, and settings |
| `app/Services/BoardTemplateService.php` | Creates personal copies of the shared board |
| `config/talkie_voices.php` | Speech synthesis voice catalog |
| `database/seeders/BoardTemplateSeeder.php` | Starter vocabulary and word categories |
| `resources/js/pages/board` | Communication board interface |
| `resources/js/pages/onboarding` | User personalization and voice selection |
| `android/` | Capacitor Android application |
| `plugins/sherpa-tts` | Native speech synthesis integration |
| `docs/android-play.md` | Android build and distribution documentation |
| `PLAN.md` | Original product roadmap and development notes |

The roadmap reflects the direction I was exploring during active development. It should not be interpreted as a commitment to future releases.

## Development

### Running Tests

```bash
php artisan test
```

### Formatting PHP

```bash
./vendor/bin/pint
```

### Linting the Frontend

```bash
npm run lint
```

### Formatting the Frontend

```bash
npm run format
```

### Resetting the Database

To rebuild the local development database and restore the starter communication board:

```bash
php artisan migrate:fresh --seed
```

**Warning:** This command removes all existing data from the configured database.

### Laravel Boost

The project includes Laravel Boost as a development dependency for AI-assisted development.

To generate local development guidelines and MCP configuration:

```bash
php artisan boost:install
```

To update the generated configuration:

```bash
php artisan boost:update
```

Generated agent configuration files are excluded from version control so they can be configured independently for each development environment.

## Design Considerations

A few principles guided the development of Talkie:

- The basic communication experience should not require an account.
- Children should be able to navigate the board without reading complicated instructions.
- Buttons should be large, visually distinct, and easy to select.
- Personalization should not interfere with the basic communication experience.
- The shared vocabulary should remain separate from individual user boards.
- The web interface should be reusable across devices wherever practical.

## Limitations

Talkie is an independent software project, not a clinically evaluated AAC product.

It has not undergone formal accessibility testing or evaluation by speech-language professionals. It should not be considered a replacement for a professionally recommended communication system.

Some functionality also depends on external services, device speech synthesis capabilities, or native voice models that may require additional configuration.

The hosted service is no longer available, and the project is not currently receiving regular maintenance or security updates.

Anyone deploying their own instance should review the application, update its dependencies, and evaluate the licensing requirements of any third-party voice models before distributing it.

## Contributing

Although I'm not actively developing Talkie, the repository remains open for anyone who finds it useful.

You're welcome to fork the project, experiment with the implementation, or adapt it to your own needs.

Issues and pull requests may not receive an immediate response while the project is inactive.

## License

Talkie is licensed under the [MIT License](LICENSE).

Third-party dependencies, native libraries, and speech synthesis models are subject to their respective licenses.
