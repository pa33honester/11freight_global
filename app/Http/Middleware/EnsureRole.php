<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request and ensure the user has one of the given roles.
     * Usage in routes: ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,finance')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Normalize roles to strings
        $roles = array_map(fn($r) => strtolower((string) $r), $roles ?: []);

        // If no roles provided, allow through (useful fallback)
        if (empty($roles)) {
            return $next($request);
        }

        // User may have roles via Spatie HasRoles trait
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
