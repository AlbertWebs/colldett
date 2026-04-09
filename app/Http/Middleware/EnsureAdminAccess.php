<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) config('colldett.admin.session_key', 'admin_panel_authenticated');

        if ($request->session()->get($key) === true) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }

        return redirect()->guest(route('admin.login'));
    }
}
