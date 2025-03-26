<?php

namespace App\Services;
use App\Jobs\SlowJob;
use App\Jobs\SimpleJob;
use App\Jobs\Version2;
use App\Jobs\RefreshDeviceJob;
use App\Jobs\LogJob;
use App\Jobs\CreationOfTicketsJob;
use App\Jobs\CommentDeviceRemoved;
use App\Jobs\CreationOfTokenJob;
use App\Jobs\UpdateStatusDBJob;
use App\Jobs\CommentInTicketsJob;
use App\Jobs\UpdateTicketsJob;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\ZohoDesk;
use App\Http\Requests\StoreZohoDeskRequest;
use App\Http\Requests\UpdateZohoDeskRequest;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\ZohoCredential;
use App\Models\SystemConfiguration;
class MySharedService
{

    public function Service_RefreshDevice($userId){
        $lockKey = 'refresh_device_job_' . $userId;

        if(Cache::add($lockKey, true, 130)){
            try {
                RefreshDeviceJob::dispatch($userId);
            } catch(\Throwable $e){
                \Log::error('Failed to dispatch Service_RefreshDevice for user ' . $userId . ': ' . $e->getMessage());
            }
        }
    }
    public function Service_Version2($userId){
        $lockKey = 'version2_job_' . $userId;

        if(Cache::add($lockKey, true, 130)){
            try {
                Version2::dispatch($userId);
            } catch(\Throwable $e){
                \Log::error('Failed to dispatch Service_Version2 for user ' . $userId . ': ' . $e->getMessage());
            }
        }
    }
    public function Service_CreateTicket($userId){
        $lockKey = 'createticket_job_'.$userId;

        if(Cache::add($lockKey, true, 130)){
            try {
                CreationOfTicketsJob::dispatch();
            } catch(\Throwable $e){
                \Log::error('Failed to dispatch Service_CreateTicket for user ' . $userId . ': ' . $e->getMessage());
            }
        }
    }
    public function Service_CommentInTicket($userId){
        $lockKey = 'commentinticket_job_'.$userId;
        $currentHour = now()->hour;
        if (($currentHour >= 10 && $currentHour < 11) || ($currentHour >= 16 && $currentHour < 17)){
            if(Cache::add($lockKey, true, 3600)){
                try {
                    $startOfDay = Carbon::today()->startOfDay();
                    $currentDay = new DateTime();
                    $endOfDay = Carbon::today()->endOfDay();

                    $Devices = $this->DeviceInfo();
                    $AccessToken = $this->CreateToken();
                    $Zoho_Credentials = $this->Zoho_Credentials();
                    $SysConCommentUpdate = $this->SystemConfiguration()->where('Code_Name', 'Ticket_Timer_Comments')->first();

                    CommentInTicketsJob::dispatch($startOfDay,$currentDay,$endOfDay,$Devices, $AccessToken,$Zoho_Credentials,$SysConCommentUpdate);
                } catch(\Throwable $e){
                    \Log::error('Failed to dispatch Service_CommentInTicket for user ' . $userId . ': ' . $e->getMessage());
                }
            }
        } 
    }
    public function Service_CommentDeviceRemoved($userId){
        $lockKey = 'commentdeviceremoved_job_'.$userId;
        $currentHour = now()->hour;
        if ($currentHour >= 11 && $currentHour < 12) {
            if(Cache::add($lockKey, true, 130)){
                try{
                    $Devices = $this->DeviceRemovedStatus();
                    $AccessToken = $this->CreateToken();
                    $Zoho_Credentials = $this->Zoho_Credentials();
                    $SysConContent = $this->SystemConfiguration()->where('Code_Name', 'Remove_Device_Comment')->first();
                
                    CommentDeviceRemoved::dispatch($Devices, $AccessToken,$Zoho_Credentials,$SysConContent );
                } catch(\Throwable $e){
                    \Log::error('Failed to dispatch Service_CommentDeviceRemoved for user ' . $userId . ': ' . $e->getMessage());
                }
            }
        }
    }
    public function Service_UpdateTicket($userId){
        $lockKey = 'updateticket_job_'.$userId;
        $currentHour = now()->hour;
        if ($currentHour >= 12 && $currentHour < 13) {
            if(Cache::add($lockKey, true, 130)){
                try{
                    $startOfDay = Carbon::today()->startOfDay();
                    $currentDay = new DateTime();
                    $endOfDay = Carbon::today()->endOfDay();

                    $AccessToken = $this->CreateToken();
                    $Zoho_Credentials = $this->Zoho_Credentials();
                    $Offline_Devices = $this->DeviceInfo();
                    $Device_Histories_CurrentYear = $this->DeviceHistory();
                    $Count = $Device_Histories_CurrentYear->count();
                    $Off_Devices =$this->Zoho_Desk_Open_Tickets();

                    UpdateTicketsJob::dispatch($startOfDay,$currentDay,$endOfDay,$AccessToken,$Zoho_Credentials,$Offline_Devices,$Device_Histories_CurrentYear,$Count,$Off_Devices);
                } catch(\Throwable $e){
                    \Log::error('Failed to dispatch Service_UpdateTicket for user ' . $userId . ': ' . $e->getMessage());
                }
            }
        }
    }
    public function Service_UpdateStatus($userId){
        $lockKey = 'updatestatus_job_'.$userId;
        $currentHour = now()->hour;
        if ($currentHour >= 13 && $currentHour < 14) {
            if(Cache::add($lockKey, true, 130)){
                try{
                    $OpenTickets = $this->Zoho_Desk_Open_Tickets();
                    $AccessToken = $this->CreateToken();
                    $Zoho_Credentials = $this->Zoho_Credentials();
                    $SysConContent = $this->SystemConfiguration()->where('Code_Name', 'Remove_Device_Comment')->first();
                
                    UpdateStatusDBJob::dispatch($OpenTickets,$AccessToken,$Zoho_Credentials,$SysConContent);
                } catch(\Throwable $e){
                    \Log::error('Failed to dispatch Service_UpdateTicket for user ' . $userId . ': ' . $e->getMessage());
                }
            }
        }
    }
    //////////////////////////////////////////////////////end of service//////////////////////////////////////////
    public function Zoho_Credentials(){
       
        return $Zoho_Credentials = DB::table('zoho_credentials')
                            ->select('code','clientID','clientSecret','access_token','refresh_token','orgID','departmentID','contactID')
                            ->first();
    }
    public function Zoho_Desk_Open_Tickets(){
        
        return $Zoho_desk_Open_Tickets = DB::table('zoho_desks')
                            ->select('Ticket_Id','Ticket_Number','Company_Id','Device_Id','Subject','Status','Remarks','Log_Last_Online','Elapse_Time')
                            ->where('Status','Open')
                            ->get();
    }
    public function Zoho_Desk_Close_Tickets(){

        return $Zoho_desk_Close_Tickets = DB::table('zoho_desks')
                            ->select('Ticket_Id','Ticket_Number','Company_Id','Device_Id','Subject','Status','Remarks','Log_Last_Online','Elapse_Time')
                            ->where('Status','Closed')
                            ->get();
    }
    public function DeviceInfo(){

        return $Devices = DB::table('Devices as a')
                            ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                            ->select('Device_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','MAC_Address','Status','a.Api_Id','a.created_at','a.updated_at')
                            ->whereNotIn('Status',['Online','OK'])
                            ->distinct()
                            ->get();
    }
    public function DeviceRemovedStatus(){

        return $Devices = DB::table('Devices as a')
                            ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                            ->select('Device_Id','Company_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','MAC_Address','Status','a.Api_Id','a.created_at','a.updated_at')
                            ->where('Status','Removed')
                            ->get();
    }
    public function SystemConfiguration(){

        return $SysCon = DB::table('system_configurations')
                        ->select('Code_ID','Code_Name','Code_Value','Code_Description')
                        ->get();
    }   
    public function DeviceHistory(){

        $currentYear = Carbon::now()->year;
        $today = Carbon::today()->toDateString();
       // dd($today);
        return $DeviceHistory = DB::table('Device_histories as a')
                                ->join('zoho_desks as b', 'a.Device_ID', '=', 'b.Device_Id')
                                ->select('a.Device_ID','a.Device_Name','a.Device_Desc','a.Device_Loc','a.Room_Type','a.Manufacturer','a.Serial_Num','a.IP_Address','a.MAC_Add','a.Status','a.Previous_Date','a.created_at','a.updated_at')
                               ->whereDate('b.created_at', $today)
                                ->where('a.Status','Offline')
                                ->where('b.Status','Open')
                                ->get();
    }
    public function CreateToken(){
        $Zoho_Credentials = $this->Zoho_Credentials();
       // CreationOfTokenJob::dispatch($Zoho_Credentials);
            try{
           // Log::Info('info', ['Itwas success' => 'Token Creation']);
                $accessTokenRequest = [
                    'form_params' => [
                        'code' => $Zoho_Credentials->code, //Authorization code obtained after generating the grant token.
                        'grant_type' => 'authorization_code',
                        'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
                        'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
                        'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
                    ]
                ];
                $client = new Client();
                $response = $client->post('https://accounts.zoho.com/oauth/v2/token', $accessTokenRequest);
            
                $accessTokenData = json_decode($response->getBody()->getContents(), true);
                if(isset($accessTokenData['error'])){
                    $accessTokenData = $this->recreateAccessToken($Zoho_Credentials);
                    $Zoho_Credentials = DB::table('zoho_credentials')
                                        ->update(['code'=>$accessTokenData]);
                }else{
                    $Zoho_Credentials = DB::table('zoho_credentials')
                                        ->update(['code'=>$accessTokenData['access_token'],
                                        'refresh_token'=>$accessTokenData['refresh_token']
                                        ]);
                }
                return $accessTokenData;
            } catch (\Throwable $e) {
                \Log::error('Error in handle Creation of token', ['exception' => $e->getMessage()]);
                throw $e;
            }
    }
    private function recreateAccessToken($Zoho_Credentials){
        try{
            $refreshTokenRequest = [
                'form_params' => [
                    'refresh_token' => $Zoho_Credentials->refresh_token, //Authorization code obtained after generating the grant token.
                    'grant_type' => 'refresh_token',
                    'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
                    'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
                    'scope' => 'Desk.tickets.CREATE,Desk.tickets.READ,Desk.tickets.UPDATE',
                    'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
                ]
            ];
            $client = new Client();
            $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
                'form_params' => $refreshTokenRequest['form_params'],
                'verify' => false
            ]);
            $accessTokenData = json_decode($response->getBody()->getContents(), true);
      
            $newAccessToken = $accessTokenData['access_token'];
        // $newRefreshToken = $accessTokenData['refresh_token'];

            $Zoho_Credentials = DB::table('zoho_credentials')
            ->update(['code'=>$newAccessToken]);
            //dd($response,$accessTokenData, $newAccessToken );
            return $newAccessToken;
        } catch (\Throwable $e) {
            \Log::error('Error in handle', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
    public function uhooAccessCode(){

        $response = Http::asForm()->post('https://api.uhooinc.com/v1/generatetoken', [
            'code' => '7369768a04b407a0cf6aa1ea5f853fa36a16d33407fee0e9'
        ]);

        if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? null;

            if ($accessToken) {
                return $accessToken;
                \Log::Info('uhooAccessCode', ['AccessCode: ' => $accessToken]); 
            } else {
                \Log::error('uhooAccessCode', ['exception' => $e->getMessage()]);            }
        } else {
            \Log::error('uhooAccessCode', ['exception' => $e->getMessage()]);        }
    }
    public function uhooDeviceList($auth,$region){
        try{
          //  $auth = Auth::user();
          //  $region = $this->region();
          //  $accessToken = $this->uhooAccessCode();
       
            $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken
            ])->get('https://api.uhooinc.com/v1/devicelist');

            $data = $response->json();
            $MacAddress =[];
            $SerialNumber =[];
            foreach($data as $data){
                if (isset($data['macAddress'])) {
                    $MacAddress[] = $data['macAddress'];
                }
                
                if (isset($data['serialNumber'])) {
                    $SerialNumber[] = $data['serialNumber'];
                }
                $existing = DB::table('uhoo_Device_Header')
                                ->select([
                                    'Serial_Number', 'Device_Name', 
                                    'Mac_Address', 'Floor_Number', 'Room_Name', 
                                    'Time_Zone', 'UTC'
                                ])
                                ->where('Serial_Number', $data['serialNumber'])
                                ->first();
                if($existing){ //this is the right way to update a data comparing existing data from the other
                    $existingArray = (array) $existing;

                    $updateData = [
                        'Serial_Number' => $data['serialNumber'],
                        'Device_Name' => $data['deviceName'],
                        'Mac_Address' => $data['macAddress'],
                        'Floor_Number' => $data['floorNumber'],
                        'Room_Name' => $data['roomName'],
                        'Time_Zone' => $data['timezone'],
                        'UTC' => $data['utcOffset'],
                    ];
                    $filteredData = array_diff_assoc($updateData, $existingArray);

                    if (!empty($filteredData)) {
                        DB::table('uhoo_Device_Header')
                            ->where('Serial_Number', $data['serialNumber'])
                            ->update($filteredData);
                    }
                }else{
                    DB::table('uhoo_Device_Header')->insert([
                        'Serial_Number' => $data['serialNumber'],
                        'Device_Name' => $data['deviceName'],
                        'Mac_Address' => $data['macAddress'],
                        'Floor_Number' => $data['floorNumber'],
                        'Room_Name' =>$data['roomName'],
                        'Time_Zone' =>$data['timezone'],
                        'UTC' =>$data['utcOffset'],
                    ]);
                }
            }
            return [
                'MacAddress' => $MacAddress,
                'SerialNumber' => $SerialNumber
            ];
        } catch(\Throwable $e){
            return response()->json([
                'Error' =>'function uhooDeviceList - '.  $e->getMessage()
            ]);
        }
    }
  
}
