<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $userRole = $request->user()?->role;
        $value = $userRole instanceof \BackedEnum ? $userRole->value : $userRole;

        abort_unless($value === $role, 403);

        return $next($request);
    }
}
