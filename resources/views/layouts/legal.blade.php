<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} — {{ config('app.name', 'Talkie') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --sky-50: #f0f9ff;
                --sky-200: #bae6fd;
                --sky-700: #0369a1;
                --sky-800: #075985;
                --orange-500: #f97316;
                --slate-600: #475569;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                min-height: 100%;
            }

            body {
                background: var(--sky-50);
                color: var(--sky-800);
                font-family: Nunito, ui-sans-serif, system-ui, sans-serif;
            }

            .legal-shell {
                display: flex;
                min-height: 100vh;
                flex-direction: column;
            }

            .legal-header,
            .legal-footer {
                background: rgba(255, 255, 255, 0.86);
                border-color: rgb(186 230 253 / 0.7);
            }

            .legal-header {
                position: sticky;
                top: 0;
                z-index: 10;
                border-bottom: 1px solid;
                padding: 0.75rem 1rem;
            }

            .legal-brand {
                display: inline-flex;
                align-items: baseline;
                color: inherit;
                text-decoration: none;
            }

            .legal-brand-name {
                font-size: 1.25rem;
                font-weight: 800;
                letter-spacing: -0.02em;
                color: var(--sky-700);
            }

            .legal-brand-tld {
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--orange-500);
            }

            .legal-main {
                flex: 1;
                width: 100%;
                max-width: 52rem;
                margin: 0 auto;
                padding: 1.5rem 1rem 2.5rem;
            }

            .legal-panel {
                overflow: hidden;
                background: #fff;
                border: 2px solid var(--sky-200);
                border-radius: 1.5rem;
                box-shadow: 0 10px 15px -3px rgb(15 23 42 / 0.08);
                padding: 1.5rem;
            }

            .legal-footer {
                border-top: 1px solid;
                padding: 1rem;
                text-align: center;
            }

            .legal-nav {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.75rem 1rem;
                margin: 0 0 0.5rem;
                padding: 0;
                list-style: none;
            }

            .legal-nav a {
                color: rgb(3 105 161 / 0.8);
                font-size: 0.875rem;
                font-weight: 700;
                text-decoration: none;
            }

            .legal-nav a:hover,
            .legal-nav a[aria-current='page'] {
                color: var(--sky-800);
                text-decoration: underline;
                text-underline-offset: 4px;
            }

            .legal-copy {
                margin: 0;
                color: var(--slate-600);
                font-size: 0.75rem;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="legal-shell">
            <header class="legal-header">
                <a href="{{ route('board') }}" class="legal-brand">
                    <span class="legal-brand-name">Talkie</span><span class="legal-brand-tld">.kids</span>
                </a>
            </header>

            <main class="legal-main">
                <div class="legal-panel">
                    @yield('content')
                </div>
            </main>

            <footer class="legal-footer">
                <ul class="legal-nav">
                    <li>
                        <a href="{{ route('privacy') }}" @if (request()->routeIs('privacy')) aria-current="page" @endif>
                            Privacy
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" @if (request()->routeIs('terms')) aria-current="page" @endif>
                            Terms
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cookies') }}" @if (request()->routeIs('cookies')) aria-current="page" @endif>
                            Cookies
                        </a>
                    </li>
                </ul>
                <p class="legal-copy">Talkie.kids</p>
            </footer>
        </div>
    </body>
</html>
