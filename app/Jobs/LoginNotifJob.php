<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Notifications\sendNotif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class LoginNotifJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user;
    private $ForgetPassData;
    private $ctr;
    /**
     * Create a new job instance.
     */
    public function __construct($user,$ForgetPassData,$ctr)
    {
        $this->user = $user;
        $this->ForgetPassData = $ForgetPassData;
        $this->ctr = $ctr;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
      
            $this->user->notify(new SendNotif($this->ForgetPassData, $this->ctr));
        } catch (\Throwable $e) {
            \Log::error('Error in handle Creation of Login Notif', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
