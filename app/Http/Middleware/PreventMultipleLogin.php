<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class PreventMultipleLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//dd($request);
        // Check if the user is logged in
$all = Auth::user(); // or auth()->user();
dd($all);
        if (Auth::check()) {

            $currentPassword = $request->input('password');

                Auth::logoutOtherDevices($currentPassword);
        }

        return $next($request);
    }
}
