<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvalidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $sessionId = Session::getId();
            // Get the current time and the last activity time from the session
            $lastActivity = $request->session()->get('last_activity');
            $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds
            
            if ($lastActivity && (time() - $lastActivity > $sessionLifetime)) {
                // Invalidate the session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                cache()->forget("user_session_id_{$userId}");
                return redirect('/login')->withErrors(['message' => 'Your session has expired due to inactivity.']);
            }

            // Update the last activity time
            $request->session()->put('last_activity', time());
        }
        return $next($request);
    }
}
