<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    {
     
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('/');
        }
       // return $next($request);
        // Check if the token is not null
        $user = Auth::user();
        //dd($user->getOriginal('remember_token'),$user->remember_token );
        if($user!=null){
            if ($user->remember_token === null) {
                return redirect('/'); // Redirect if the token is null
            }
        $response = $next($request);

        return $response;
    }else{
        $response = $next($request);

        // // Set cache control headers to ensure no caching
        // $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        // $response->headers->set('Pragma', 'no-cache');
        // $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
    }
}
