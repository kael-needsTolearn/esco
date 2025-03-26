<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Jobs\Heartbeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HomeController;
use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\DeviceRoom;
use App\Models\ZohoDesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\UserAccess;
use Carbon\Carbon;
use Faker\Provider\ar_EG\Company;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Arr;
use App\Notifications\Notifications;
use App\Notifications\sendNotif;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SlowJob;
use App\Jobs\SimpleJob;
use App\Jobs\Version2;
use App\Jobs\RefreshDeviceJob;
use App\Jobs\LogJob;
use App\Http\Requests\ValidateSession;
use App\Jobs\LoginNotifJob;
use App\Http\Controllers\SystemConfig;
use App\Services\MySharedService;
use Illuminate\Http\Client\RequestException;

// Schedule::call(function () {
//     try {
//         $auth = Auth::user();
//         \Log::info('INFO::', ['info4: ' => $auth]);
//         $controller = app(HomeController::class);
//         $controller->uhoo_create_history();
//         Log::info('corrected ');
//     } catch (\Throwable $e) {
//         Log::error('Error in uhoo:update command: ' . $e->getMessage(), [
//             'stack' => $e->getTraceAsString()
//         ]);
//         $this->error('Error executing uhoo_create_history: ' . $e->getMessage());
//     }
// })->everyMinute();

//Schedule::command('uhoo:update')->everyMinute();
