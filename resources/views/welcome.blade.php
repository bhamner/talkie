<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @if (filled($ga4Id = config('services.ga4.id')))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga4Id) }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', @json($ga4Id));
            </script>
        @endif
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="color-scheme" content="light">
        <meta name="theme-color" content="#0369a1">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Talkie') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <title>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Talkie">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:image" content="{{ $imageUrl }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1920">
        <meta property="og:image:height" content="1080">
        <meta property="og:image:alt" content="{{ $imageAlt }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $imageUrl }}">
        <meta name="robots" content="max-image-preview:large">
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}</script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:500,600,700,800" rel="stylesheet" />
        @vite(['resources/css/app.css'])
    </head>
    <body class="welcome-page font-sans text-slate-800 antialiased">
        <header class="border-b border-sky-200/70 bg-white">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2" aria-label="Talkie home">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-white shadow-sm ring-2 ring-sky-200">
                        <img src="{{ asset('images/talkie-logo.svg') }}" alt="" class="size-8" />
                    </span>
                    <span class="hidden items-baseline leading-none lg:flex">
                        <span class="text-xl font-extrabold tracking-tight text-sky-700">Talkie</span>
                        <span class="text-xs font-semibold text-orange-500">.kids</span>
                    </span>
                </a>
                <nav class="flex items-center gap-2" aria-label="Primary">
                    <a href="{{ route('board') }}" class="inline-flex h-10 items-center justify-center rounded-full border-2 border-sky-200 bg-white px-4 text-sm font-extrabold text-slate-800 shadow-sm hover:bg-sky-50">Open the board</a>
                    @guest
                        <a href="{{ route('personalize') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-primary px-4 text-sm font-extrabold text-primary-foreground shadow-md hover:bg-primary/90">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>
                            Personalize
                        </a>
                    @endguest
                </nav>
            </div>
        </header>

        <main>
            <section class="relative isolate overflow-hidden bg-sky-50">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $imageAlt }}"
                    width="1920"
                    height="1080"
                    fetchpriority="high"
                    decoding="async"
                    class="absolute inset-y-0 right-0 h-full w-auto max-w-none"
                />
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f9ff_0%,#f0f9ff_62%,rgb(240_249_255/0.2)_100%)] lg:bg-[linear-gradient(to_right,#f0f9ff_0%,#f0f9ff_34%,transparent_68%)]" aria-hidden="true"></div>
                <div class="relative mx-auto flex min-h-[28rem] w-full max-w-5xl items-center px-4 py-16 sm:min-h-[32rem] sm:py-20">
                    <div class="flex max-w-xl flex-col gap-5">
                        <p class="text-sm font-extrabold uppercase tracking-wide text-orange-500">Talkie.kids</p>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-800 sm:text-5xl">
                            A free text to speech board for children who are learning to communicate
                        </h1>
                        <p class="text-lg font-semibold leading-relaxed text-slate-600">
                            Tap a picture and Talkie says the word out loud using your device's text to speech voice. No account required.
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('board') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-primary px-6 text-base font-extrabold text-primary-foreground shadow-md hover:bg-primary/90">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg>
                                Try it!
                            </a>
                            <a href="#word-lists" class="inline-flex h-12 items-center justify-center rounded-full border-2 border-sky-200 bg-white px-6 text-base font-extrabold text-slate-800 shadow-sm hover:bg-sky-50">More info</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white" aria-labelledby="how-it-works">
                <div class="mx-auto w-full max-w-5xl px-4 py-12 sm:py-16">
                    <h2 id="how-it-works" class="text-2xl font-extrabold tracking-tight sm:text-3xl">How it works</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <article class="rounded-3xl border-2 border-sky-200 bg-sky-50 p-5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg>
                            <h3 class="mt-3 text-lg font-extrabold">Tap a word</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">
                                Each button is a word with a picture. One tap speaks it and places it in the sentence bar at the top.
                            </p>
                        </article>
                        <article class="rounded-3xl border-2 border-sky-200 bg-sky-50 p-5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                            <h3 class="mt-3 text-lg font-extrabold">Build a sentence</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">
                                Add more words, then press Speak to hear the whole phrase. Endings such as plural “s” and “-ing” can attach to the last word.
                            </p>
                        </article>
                        <article class="rounded-3xl border-2 border-sky-200 bg-sky-50 p-5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 14 1.5-2.9A2 2 0 0 1 9.24 10H20a2 2 0 0 1 1.94 2.5l-1.54 6a2 2 0 0 1-1.95 1.5H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H18a2 2 0 0 1 2 2v2"/></svg>
                            <h3 class="mt-3 text-lg font-extrabold">Personalize</h3>
                            <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-600">
                                Create an account to customize the speaking voice and save your own words, phrases, and folders.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="word-lists" class="bg-orange-50" aria-labelledby="word-lists-heading">
                <div class="mx-auto flex w-full max-w-5xl flex-col gap-4 px-4 py-12 sm:py-16">
                    <h2 id="word-lists-heading" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Starting with the words children actually use</h2>
                    <p class="max-w-3xl text-base font-semibold leading-relaxed text-slate-600">
                        The board vocabulary starts with words from two well-known studies, 
                        and is built from advisory sessions with licensed children's speech-language pathologists.
                    </p>

                    <div class="grid gap-3 lg:grid-cols-2">
                        <article class="rounded-3xl border-2 border-orange-200 bg-white p-5 shadow-sm">
                            <h3 class="mt-2 text-xl font-extrabold">Banajee, DiCarlo, and Stricklin</h3>
                            <p class="mt-1 text-sm font-bold text-orange-800">Toddler core words, 2003</p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">
                                This study looked specifically at toddlers (24 to 36 months). They tracked the unprompted, natural speech of 50 toddlers across different childcare centers during two distinct daily routines: free play and snack time. It identified a core of 23 words that accounted for 96% of all the words used by the children during their preschool routines.
                            </p>
                        </article>

                        <article class="rounded-3xl border-2 border-sky-200 bg-white p-5 shadow-sm">
                            <h3 class="mt-2 text-xl font-extrabold">Marvin, Beukelman, and Bilyeu</h3>
                            <p class="mt-1 text-sm font-bold text-sky-800">Preschool words children use most</p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">
                                This study is a foundational piece of research in the field of Augmentative and Alternative Communication (AAC). Titled "Vocabulary-use patterns in preschool children: Effects of context and time sampling," this study analyzed the natural, expressive language of typically developing preschoolers at home and at school.
                            </p>
                            <p class="mt-3 text-sm font-semibold leading-relaxed text-slate-700">
                                Its findings heavily shaped how modern AAC communication boards and high-tech devices are programmed.
                            </p>
                        </article>
                    </div>

                    
                </div>
            </section>


            <section class="bg-orange-500 text-white">
                <div class="mx-auto w-full max-w-5xl px-4 py-12 text-center sm:py-16">
                    <h2 class="text-2xl font-extrabold">Start with the words already on the board</h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm font-semibold text-orange-50">
                        Open Talkie and tap a word. Personalize it to make it your own.
                    </p>
                    <a href="{{ route('board') }}" class="mt-4 inline-flex h-12 items-center justify-center rounded-full bg-white px-6 text-base font-extrabold text-slate-800 shadow-md hover:bg-orange-50">Open the board</a>
                </div>
            </section>
        </main>

        <footer class="bg-sky-800 px-4 py-6 text-center text-sm font-semibold text-sky-100">
            <nav class="flex flex-wrap items-center justify-center gap-4" aria-label="Legal">
                <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                <a href="{{ route('terms') }}" class="hover:text-white">Terms</a>
                <a href="{{ route('cookies') }}" class="hover:text-white">Cookies</a>
            </nav>
        </footer>
    </body>
</html>
