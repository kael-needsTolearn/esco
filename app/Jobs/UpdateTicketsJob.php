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
class UpdateTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $startOfDay;
    private $currentDay;
    private $endOfDay;
    private $AccessToken;
    private $Zoho_Credentials;
    private $Offline_Devices;
    private $Device_Histories_CurrentYear;
    private $Count;
    private $Off_Devices;
    /**
     * Create a new job instance.
     */
    public function __construct($startOfDay,$currentDay,$endOfDay,$AccessToken,$Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices)
    {
        $this->startOfDay = $startOfDay;
        $this->currentDay = $currentDay;
        $this->endOfDay = $endOfDay;
        $this->AccessToken = $AccessToken;
        $this->AccessToken = $AccessToken;
        $this->Zoho_Credentials = $Zoho_Credentials;
        $this->Offline_Devices = $Offline_Devices;
        $this->Device_Histories_CurrentYear = $Device_Histories_CurrentYear;
        $this->Count = $Count;
        $this->Off_Devices = $Off_Devices;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startOfDay = $this->startOfDay;
        $currentDay = $this->currentDay;
        $endOfDay = $this->endOfDay;
        $AccessToken =$this->AccessToken;
        $Zoho_Credentials =$this->Zoho_Credentials;
        $Offline_Devices = $this->Offline_Devices;
        $Device_Histories_CurrentYear=$this->Device_Histories_CurrentYear ;
        $Count=$this->Count ;
        $Off_Devices=$this->Off_Devices ;

    try{  
       // $this->ExecUpdateTicket();

      // if($currentDay>=$startOfDay->copy()->addHours(4) && $currentDay<=$startOfDay->copy()->addHours(5)){
        $this->ExecUpdateTicket($startOfDay,$currentDay,$endOfDay, $AccessToken, $Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices );
            // dd("4am");
      // }else if($currentDay>=$startOfDay->copy()->addHours(10) && $currentDay<=$startOfDay->copy()->addHours(12)){
          //  $this->ExecUpdateTicket($startOfDay,$currentDay,$endOfDay, $AccessToken, $Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices);
            // dd("10am");
       // }else if($currentDay>=$startOfDay->copy()->addHours(14) && $currentDay<=$startOfDay->copy()->addHours(16)){
          //  $this->ExecUpdateTicket($startOfDay,$currentDay,$endOfDay, $AccessToken, $Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices);
            // dd("2pm");
       // }else if($currentDay>=$startOfDay->copy()->addHours(22) && $currentDay<=$startOfDay->copy()->addHours(23)){
         //   $this->ExecUpdateTicket($startOfDay,$currentDay,$endOfDay, $AccessToken, $Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices);
           // dd("11pm");
        //}
        //End of System Update
        } catch (\Throwable $e) {
            \Log::error('Error in handle UpdateTickets', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
    public function ExecUpdateTicket($startOfDay,$currentDay,$endOfDay, $AccessToken, $Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices){
            foreach($Off_Devices as $Off_Device){
                $lastRecord = DB::table('device_histories as a')
                                ->join('zoho_desks as b', 'a.Device_ID', '=', 'b.Device_Id')
                                ->join('devices as c','b.Device_ID', '=', 'c.Device_Id')
                                ->select('b.Ticket_Id','a.Device_ID','a.Device_Name','a.Device_Desc','a.Device_Loc','a.Room_Type','a.Manufacturer','a.Serial_Num','a.IP_Address','a.MAC_Add','a.Status','a.Previous_Date','a.created_at','a.updated_at')
                                ->where('a.Device_Id',$Off_Device->Device_Id)
                                ->where('b.Status','Open')
                                ->where('a.Status','Offline')
                                ->where('c.Status','Online')
                                ->latest('a.created_at')
                                ->first();
            if ($lastRecord !== null) {
                $dateStart = new DateTime($lastRecord->created_at);
                $dateEnd = new DateTime(); // Current date and time
                $interval = $dateEnd->getTimestamp() - $dateStart->getTimestamp();
                //86400 means one day or 24 hrs
                if(86400<=$interval){
                //The system will resolve the ticket itself
                $ticketId = $lastRecord->Ticket_Id;
                    $response = Http::withHeaders([
                        'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                        'orgId' => $Zoho_Credentials->orgID,
                        'Content-Type' => 'application/json',
                    ])->patch("https://desk.zoho.com/api/v1/tickets/{$ticketId}", 
                    [ 
                    'statusType' => 'Closed',
                    'status' => 'Resolved by ESCO CARE 360',
                    ]);
                    $statusCode = $response->status();
                    $responseData = $response->json();

                    DB::table('zoho_desks')
                    ->where('Ticket_Id', $ticketId)
                    ->update(['Status' => 'Closed',
                            'Remarks' => 'Resolved By ESCO CARE 360',
                            'updated_at'=>DB::raw('NOW()')]);
                }else{
                }
            }
        }
    }
}
