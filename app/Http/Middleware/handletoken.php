<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ApiRequest;
use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\DeviceRoom;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\ZohoDesk;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Notifications\sendNotif;
class handletoken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $response = $next($request);

         // Set cache control headers to ensure no caching
         $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
         $response->headers->set('Pragma', 'no-cache');
         $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
         
         return $response;
    }
}
