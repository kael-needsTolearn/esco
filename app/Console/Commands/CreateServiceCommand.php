<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Log;
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
use Illuminate\Support\Facades\DB;
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
use Illuminate\Http\Client\RequestException;
use App\Jobs\uhooUpdate;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
class CreateServiceCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uhoo:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the uhooUpdater function in HomeController';

    /**
     * Execute the console command.
     */
    public function handle(Schedule $schedule)
    {
        try {
            Schedule::call(function(){
                Log::info('huh executed successfully.');
            })->everySecond();

            Log::info('uhoo_create_history executed successfully.');
        } catch (\Throwable $e) {
            Log::error('Error in uhoo_create_history: ' . $e->getMessage());
            throw $e;
        }
    }
}
