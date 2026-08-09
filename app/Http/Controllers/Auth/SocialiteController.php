<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BoardTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialiteController extends Controller
{
    /** @var list<string> */
    private array $providers = ['google', 'apple', 'facebook'];

    public function redirect(string $provider): SymfonyRedirectResponse|RedirectResponse
    {
        $this->ensureValidProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, BoardTemplateService $boardTemplate): RedirectResponse
    {
        $this->ensureValidProvider($provider);

        $socialUser = Socialite::driver($provider)->user();

        $user = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user && $socialUser->getEmail()) {
            $user = User::query()->where('email', $socialUser->getEmail())->first();
        }

        if ($user) {
            $user->forceFill([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail() ?: $user->email,
                'email_verified_at' => $user->email_verified_at ?? now(),
                'name' => $user->name ?: ($socialUser->getName() ?: 'Talkie User'),
            ])->save();
        } else {
            $user = User::create([
                'name' => $socialUser->getName() ?: 'Talkie User',
                'email' => $socialUser->getEmail() ?: $provider.'-'.$socialUser->getId().'@users.talkie.local',
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]);
        }

        $boardTemplate->copyToUser($user);

        Auth::login($user, true);

        return redirect()->route('onboarding.gate');
    }

    private function ensureValidProvider(string $provider): void
    {
        abort_unless(in_array($provider, $this->providers, true), 404);
    }
}
