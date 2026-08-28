<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BoardTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialiteController extends Controller
{
    /** @var list<string> */
    private array $providers = ['google', 'apple'];

    public function redirect(Request $request, string $provider): SymfonyRedirectResponse|RedirectResponse
    {
        $this->ensureValidProvider($provider);

        if ($request->boolean('native')) {
            $request->session()->put('capacitor_oauth', true);
        }

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

        if (session()->pull('capacitor_oauth')) {
            $token = Str::random(64);
            Cache::put('capacitor-auth:'.$token, $user->id, now()->addMinutes(2));

            return redirect()->away('talkie://auth?token='.$token);
        }

        return redirect()->route('onboarding.gate');
    }

    public function nativeHandoff(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        abort_unless(is_string($token) && preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1, 403);

        $userId = Cache::pull('capacitor-auth:'.$token);
        abort_unless(is_numeric($userId), 403);

        $user = User::query()->findOrFail((int) $userId);

        Auth::login($user, true);

        return redirect()->route('onboarding.gate');
    }

    private function ensureValidProvider(string $provider): void
    {
        abort_unless(in_array($provider, $this->providers, true), 404);
    }
}
