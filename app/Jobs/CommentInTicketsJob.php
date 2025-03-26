<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\ZohoDesk;
use App\Http\Requests\StoreZohoDeskRequest;
use App\Http\Requests\UpdateZohoDeskRequest;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\ZohoCredential;
use App\Models\SystemConfiguration;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Collection;
use App\Http\Requests\ApiRequest;
use App\Models\ApiAccount;
use App\Models\DeviceRoom;
use App\Models\User;
use App\Models\UserAccess;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Notifications\sendNotif;
use Illuminate\Http\RedirectResponse;
use App\Jobs\LoginNotifJob;
use App\Jobs\RefreshDeviceJob;
use App\Jobs\LogJob;
class CommentInTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $startOfDay;
    private $currentDay;
    private $endOfDay;
    private $Devices;
    private $AccessToken;
    private $Zoho_Credentials;
    private $SysConCommentUpdate;
    /**
     * Create a new job instance.
     */
    public function __construct($startOfDay,$currentDay,$endOfDay,$Devices, $AccessToken,$Zoho_Credentials,$SysConCommentUpdate)
    {
        $this->startOfDay = $startOfDay;
        $this->currentDay = $currentDay;
        $this->endOfDay = $endOfDay;
        $this->Devices = $Devices;
        $this->AccessToken = $AccessToken;
        $this->Zoho_Credentials = $Zoho_Credentials;
        $this->SysConCommentUpdate = $SysConCommentUpdate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startOfDay = $this->startOfDay;
         $currentDay = $this->currentDay;
         $endOfDay = $this->endOfDay;
         $Devices =$this->Devices;
         $AccessToken =$this->AccessToken;
        $Zoho_Credentials = $this->Zoho_Credentials;
         $SysConCommentUpdate=$this->SysConCommentUpdate ;
        try{
            //if($currentDay>=$startOfDay->copy()->addHours(8) && $currentDay<=$startOfDay->copy()->addMinutes(490)){
                $this->CommentInTicketsExtension($startOfDay,$currentDay,$endOfDay,$Devices,$AccessToken,$Zoho_Credentials,$SysConCommentUpdate);
           // }else if($currentDay>=$startOfDay->copy()->addHours(16) && $currentDay<=$startOfDay->copy()->addMinutes(980)){
              //  $this->CommentInTicketsExtension($startOfDay,$currentDay,$endOfDay,$Devices,$AccessToken,$Zoho_Credentials,$SysConCommentUpdate);
           // }else if($currentDay>=$startOfDay->copy()->addHours(22) && $currentDay<=$startOfDay->copy()->addHours(1330)){
               // $this->CommentInTicketsExtension($startOfDay,$currentDay,$endOfDay,$Devices,$AccessToken,$Zoho_Credentials,$SysConCommentUpdate);
           // }
        } catch (\Throwable $e) {
            \Log::error('Error in handle CommentInTicketsExtension', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
    private function CommentInTicketsExtension($startOfDay,$currentDay,$endOfDay,$Devices,$AccessToken,$Zoho_Credentials,$SysConCommentUpdate){
        Log::Info('info', ['Itwas success' => 'CommentInTicketsExtension']);

        foreach($Devices as $Device){
           
            $Zoho_Ticket = DB::table('zoho_desks')
                            ->select('Ticket_Id','Ticket_Number','Device_Id')
                            ->where('Device_Id',$Device->Device_Id)
                            ->where('Status','Open')
                            ->first();
    
            if($Device->Status == 'Offline' && $Zoho_Ticket->Device_Id == $Device->Device_Id){
                $commentData = [
                    'isPublic' => false,
                    'attachmentIds' => null,
                    'contentType' => 'html',
                    'content' => "The device still offline"."<br>"."<br>".
                                "Direct Link: "."<a href=".url('/reports').">Click this link to redirect</a>",
                ];
                $client = new Client();
                $response = $client->post("https://desk.zoho.com/api/v1/tickets/{$Zoho_Ticket->Ticket_Id}/comments", [
                    'verify' => false,
                    'headers' => [
                        'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                        'Content-Type' => 'application/json',
                        'orgId' => $Zoho_Credentials->orgID 
                    ],
                    'json' => $commentData,
                ]);
                $statusCode = $response->getStatusCode();
                $responseBody = json_decode($response->getBody()->getContents());
                        //assume it coommented on the ticket
              //  \Log::info('Equipment still offline', ['exception' => $commentData]);
            }else{
                    $commentData = [
                        'isPublic' => false,
                        'attachmentIds' => null,
                        'contentType' => 'html',
                        'content' => "The device is ".$Device->Status."<br>"."<br>".
                                    "Direct Link: "."<a href=".url('/reports').">Click this link to redirect</a>",
                    ];
                    $client = new Client();
                    $response = $client->post("https://desk.zoho.com/api/v1/tickets/{$Zoho_Ticket->Ticket_Id}/comments", [
                        'verify' => false,
                        'headers' => [
                            'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                            'Content-Type' => 'application/json',
                            'orgId' => $Zoho_Credentials->orgID 
                        ],
                        'json' => $commentData,
                    ]);
                    $statusCode = $response->getStatusCode();
                    $responseBody = json_decode($response->getBody()->getContents());
                   //\Log::info('Equipment still'.$Device->Status, ['exception' => $commentData]);
                }
            }

    }
}
