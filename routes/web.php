<?php

use App\Http\Controllers\Auth\PersonalizeController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\Onboarding\GateController;
use App\Http\Controllers\Onboarding\NameController;
use App\Http\Controllers\Onboarding\VoiceController as OnboardingVoiceController;
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

    Route::get('onboarding', GateController::class)->name('onboarding.gate');

    Route::get('onboarding/name', [NameController::class, 'edit'])->name('onboarding.name');
    Route::put('onboarding/name', [NameController::class, 'update'])->name('onboarding.name.update');

    Route::get('onboarding/voice', [OnboardingVoiceController::class, 'edit'])->name('onboarding.voice');
    Route::put('onboarding/voice', [OnboardingVoiceController::class, 'update'])->name('onboarding.voice.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
