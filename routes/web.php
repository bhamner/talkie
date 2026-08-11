<?php

use App\Http\Controllers\Auth\PersonalizeController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Onboarding\GateController;
use App\Http\Controllers\Onboarding\NameController;
use App\Http\Controllers\Onboarding\VoiceController as OnboardingVoiceController;
use App\Http\Controllers\PhraseController;
use App\Http\Controllers\WordController;
use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/board')->name('home');

Route::get('board/{menu?}', [BoardController::class, 'show'])
    ->middleware(EnsureOnboardingComplete::class)
    ->name('board');

Route::middleware('guest')->group(function () {
    Route::get('personalize', PersonalizeController::class)->name('personalize');

    Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('socialite.redirect');
    Route::match(['get', 'post'], 'auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('socialite.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('onboarding.gate');
    })->name('dashboard');

    Route::middleware(EnsureOnboardingComplete::class)->group(function () {
        Route::post('phrases', [PhraseController::class, 'store'])->name('phrases.store');
        Route::delete('phrases/{phrase}', [PhraseController::class, 'destroy'])->name('phrases.destroy');
        Route::post('phrases/{phrase}/hide', [PhraseController::class, 'hide'])->name('phrases.hide');
        Route::post('phrases/{phrase}/unhide', [PhraseController::class, 'unhide'])->name('phrases.unhide');

        Route::post('words', [WordController::class, 'store'])->name('words.store');
        Route::put('words/{word}', [WordController::class, 'update'])->name('words.update');
        Route::delete('words/{word}', [WordController::class, 'destroy'])->name('words.destroy');
        Route::post('words/{word}/hide', [WordController::class, 'hide'])->name('words.hide');
        Route::post('words/{word}/unhide', [WordController::class, 'unhide'])->name('words.unhide');
        Route::post('words/{word}/move', [WordController::class, 'move'])->name('words.move');

        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('menus/{menu}/move', [MenuController::class, 'move'])->name('menus.move');
    });

    Route::get('onboarding', GateController::class)->name('onboarding.gate');

    Route::get('onboarding/name', [NameController::class, 'edit'])->name('onboarding.name');
    Route::put('onboarding/name', [NameController::class, 'update'])->name('onboarding.name.update');

    Route::get('onboarding/voice', [OnboardingVoiceController::class, 'edit'])->name('onboarding.voice');
    Route::put('onboarding/voice', [OnboardingVoiceController::class, 'update'])->name('onboarding.voice.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
