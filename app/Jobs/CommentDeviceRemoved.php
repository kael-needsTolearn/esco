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
class CommentDeviceRemoved implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $Devices;
    protected $AccessToken;
    protected $Zoho_Credentials;
    protected $SysConContent;
    /**
     * Create a new job instance.
     */
    public function __construct($Devices,$AccessToken,$Zoho_Credentials,$SysConContent)
    {
        $this->Devices = $Devices;
        $this->AccessToken = $AccessToken;
        $this->Zoho_Credentials = $Zoho_Credentials;
        $this->SysConContent = $SysConContent;
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $Devices = $this->Devices;
        $AccessToken = $this->AccessToken;
        $Zoho_Credentials =$this->Zoho_Credentials;
        $SysConContent = $this->SysConContent;

        try{
           // Log::Info('info', ['It sdasdwas success' => $Zoho_Ticket->Ticket_Id,$Device->Device_Id]);

            if($Devices){
                foreach($Devices as $Device){
           // Log::Info('info', ['Devices' => $Device->Device_Id]);

                $Zoho_Ticket = DB::table('zoho_desks')
                                ->select('Ticket_Id','Ticket_Number','Device_Id')
                                ->where('Device_Id',$Device->Device_Id)
                                ->where('Status','Open')
                                ->first();
                  //  Log::Info('info', ['zoho ticket id and device id' => $Zoho_Ticket]);
                $commentData = [
                    'isPublic' => false,
                    'attachmentIds' => null,
                    'contentType' => 'html',
                    'content' => ($SysConContent->Code_Value), // Adjust as per your form or request data
                ];
                    if($Zoho_Ticket){
                        $client = new Client();
                        $response = $client->post("https://desk.zoho.com/api/v1/tickets/{$Zoho_Ticket->Ticket_Id}/comments", [
                            'verify' => false,
                            'headers' => [
                                'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                                'Content-Type' => 'application/json',
                                'orgId' => $Zoho_Credentials->orgID // Add orgId to the headers
                            ],
                            'json' => $commentData,
                        ]);
                        $statusCode = $response->getStatusCode();
                        $responseBody = json_decode($response->getBody()->getContents());
                        if($statusCode == 200){
                            // return response()->json([
                            //     'Success' => "device was removed",
                            // ]);
                        }       
                    }    
                }//end of loop of devices thats status removed
            }
        } catch (\Throwable $e) {
            \Log::error('Error in handle', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
