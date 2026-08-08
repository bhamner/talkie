<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Redirect authenticated users who have not finished onboarding.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasCompletedOnboarding()) {
            return $next($request);
        }

        return match ($user->onboardingStep()) {
            'name' => redirect()->route('onboarding.name'),
            'voice' => redirect()->route('onboarding.voice'),
            default => $next($request),
        };
    }
}
