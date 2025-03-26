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
class UpdateStatusDBJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $OpenTickets;
    private $AccessToken;
    private $Zoho_Credentials;
    private $SysConContent;
    /**
     * Create a new job instance.
     */
    public function __construct($OpenTickets,$AccessToken,$Zoho_Credentials,$SysConContent)
    {
        //
        $this->OpenTickets = $OpenTickets;
        $this->AccessToken = $AccessToken;
        $this->Zoho_Credentials = $Zoho_Credentials;
        $this->SysConContent = $SysConContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $OpenTickets = $this->OpenTickets;
        $AccessToken = $this->AccessToken;
        $Zoho_Credentials = $this->Zoho_Credentials;
        $SysConContent = $this->SysConContent;
        try{
            Log::Info('info', ['Itwas success' => 'UpdateStatuss from zoho to db']);
            if($OpenTickets){
                foreach($OpenTickets as $OpenTicket){
                    $client = new Client();
                    $response = $client->GET("https://desk.zoho.com/api/v1/tickets/{$OpenTicket->Ticket_Id}", [
                        'verify' => false,
                        'headers' => [
                            'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                            'Content-Type' => 'application/json',
                            'orgId' => $Zoho_Credentials->orgID // Add orgId to the headers
                        ],
                    ]);
                    $statusCode = $response->getStatusCode();
                    $responseBody = json_decode($response->getBody()->getContents());
                    DB::table('zoho_desks')
                    ->where('Ticket_Id', $OpenTicket->Ticket_Id)
                    ->update(['Status' => $responseBody->statusType,
                            'Remarks' => $responseBody->status]);  
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error in handle upDATE STATUS Normal status since we dont have permission to update the status to zoho', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
    
}
