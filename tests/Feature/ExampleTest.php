<?php

use App\Support\WelcomePage;

test('the welcome page is the public home page', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewIs('welcome')
        ->assertViewHas('faqs', WelcomePage::faqs())
        ->assertSee('<h1', false)
        ->assertSee('A picture board that talks, so your child can say what they mean', false)
        ->assertDontSee('data-page', false)
        ->assertSee(WelcomePage::title(), false)
        ->assertSee(WelcomePage::description(), false)
        ->assertSee(WelcomePage::imageUrl(), false)
        ->assertSee(WelcomePage::imageAlt(), false)
        ->assertSee('og:image', false)
        ->assertSee('max-image-preview:large', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('bg-sky-50', false)
        ->assertSee('bg-orange-50', false)
        ->assertSee('bg-sky-100', false)
        ->assertSee('bg-violet-50', false)
        ->assertSee('bg-orange-500', false)
        ->assertSee('Banajee', false)
        ->assertSee('DiCarlo', false)
        ->assertSee('Stricklin', false)
        ->assertSee('Marvin', false)
        ->assertSee('Beukelman', false)
        ->assertSee('Bilyeu', false)
        ->assertSee('speech-language pathologists', false)
        ->assertSee('FAQPage', false)
        ->assertSee('favicon.ico', false)
        ->assertSee('apple-touch-icon.png', false)
        ->assertSee('site.webmanifest', false)
        ->assertSee('apple-mobile-web-app-title', false);

    expect(public_path('favicon.ico'))->toBeReadableFile()
        ->and(public_path('favicon.svg'))->toBeReadableFile()
        ->and(public_path('apple-touch-icon.png'))->toBeReadableFile()
        ->and(public_path('icons/icon-192.png'))->toBeReadableFile()
        ->and(public_path('icons/icon-512.png'))->toBeReadableFile();
});
