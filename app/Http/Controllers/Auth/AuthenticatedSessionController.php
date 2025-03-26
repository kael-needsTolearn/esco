<?php

namespace App\Http\Controllers\Auth;

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
use Illuminate\Http\Request;
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
use App\Jobs\LogJob;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
       
        $request->authenticate();

        $request->session()->regenerate();

        return redirect('/LoginAuth');
     // return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {

        $user = Auth::user();
        LogJob::dispatch($user,'Logout');  
 	//$userId = Auth::id(); // Get the user ID
    	//if ($userId) {
       	//   cache()->forget("user_session_id_{$userId}"); // Clear the stored session ID from cache
  	//}    
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $updateCount = DB::table('users')
        ->where('email', $user->email)
        ->update(['remember_token' => null]);
       // Session::flush();
       
        return redirect('/');
    }
}
