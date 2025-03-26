<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Http;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Jobs\CreationOfTicketsJob;
use App\Jobs\CommentDeviceRemoved;
use App\Jobs\CreationOfTokenJob;
use App\Jobs\UpdateStatusDBJob;
use App\Jobs\CommentInTicketsJob;
use App\Jobs\UpdateTicketsJob;
class ZohoDeskController extends Controller
{
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
        // dd($accessTokenData);
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
    // public function CreateToken(){
    //     try{
    //         $Zoho_Credentials = $this->Zoho_Credentials();

    //         $accessTokenRequest = [
    //             'form_params' => [
    //                 'code' => $Zoho_Credentials->code, //Authorization code obtained after generating the grant token.
    //                 'grant_type' => 'authorization_code',
    //                 'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
    //                 'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
    //                 'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
    //             ]
    //         ];
    //         $client = new Client();
    //         $response = $client->post('https://accounts.zoho.com/oauth/v2/token', $accessTokenRequest);
        
    //         $accessTokenData = json_decode($response->getBody()->getContents(), true);
    //         //dd($accessTokenData,$accessTokenData['access_token'],$accessTokenData['refresh_token']);
    //         if(isset($accessTokenData['error'])){
    //             $accessTokenData = $this->recreateAccessToken();
    //             $Zoho_Credentials = DB::table('zoho_credentials')
    //                                 ->update(['code'=>$accessTokenData]);
    //         }else{
    //             $Zoho_Credentials = DB::table('zoho_credentials')
    //                                 ->update(['code'=>$accessTokenData['access_token'],
    //                                 'refresh_token'=>$accessTokenData['refresh_token']
    //                                 ]);
    //         }
    //         //$accessToken = $accessTokenData->access_token;
    //         //dd($accessTokenData);
    //         return $accessTokenData;
    //     }catch(\Throwable $e){
    //         return response()->json([
    //         'error'=>'function RetrieveTickets - '.$e->getMessage()
    //         ]);
    //     }
    // }
    // public function recreateAccessToken(){
    //     try{
    //         $Zoho_Credentials = $this->Zoho_Credentials();
    //         $refreshTokenRequest = [
    //             'form_params' => [
    //                 'refresh_token' => $Zoho_Credentials->refresh_token, //Authorization code obtained after generating the grant token.
    //                 'grant_type' => 'refresh_token',
    //                 'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
    //                 'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
    //                 'scope' => 'Desk.tickets.CREATE,Desk.tickets.READ,Desk.tickets.UPDATE',
    //                 'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
    //             ]
    //         ];
    //     // dd($refreshTokenRequest);
    //         $client = new Client();
    //         $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
    //             'form_params' => $refreshTokenRequest['form_params'],
    //             'verify' => false
    //         ]);
    //         $accessTokenData = json_decode($response->getBody()->getContents(), true);
    //     // dd($accessTokenData);
    //         $newAccessToken = $accessTokenData['access_token'];
    //     // $newRefreshToken = $accessTokenData['refresh_token'];

    //         $Zoho_Credentials = DB::table('zoho_credentials')
    //         ->update(['code'=>$newAccessToken]);
    //         //dd($response,$accessTokenData, $newAccessToken );
    //         return $newAccessToken;
    //     }catch(\Throwable $e){
    //         return response()->json([
    //         'error'=>'function RetrieveTickets - '.$e->getMessage()
    //         ]);
    //     }
    // }
    public function RetrieveTickets(){
        $OpenTickets = $this->Zoho_Desk_Open_Tickets();
        $AccessToken = $this->CreateToken();
        $Zoho_Credentials = $this->Zoho_Credentials();
        $SysConContent = $this->SystemConfiguration()->where('Code_Name', 'Remove_Device_Comment')->first();
       // dd($OpenTickets,$AccessToken,$Zoho_Credentials,$SysConContent);
        UpdateStatusDBJob::dispatch($OpenTickets,$AccessToken,$Zoho_Credentials,$SysConContent);
    }
    public function CreationOfTickets(){
        CreationOfTicketsJob::dispatch();
    }
    
    public function CommentDeviceRemoved(){
        $Devices = $this->DeviceRemovedStatus();
        $AccessToken = $this->CreateToken();
        $Zoho_Credentials = $this->Zoho_Credentials();
        $SysConContent = $this->SystemConfiguration()->where('Code_Name', 'Remove_Device_Comment')->first();
       
        CommentDeviceRemoved::dispatch($Devices, $AccessToken,$Zoho_Credentials,$SysConContent );
    }
   
    public function CommentInTickets(){
        $startOfDay = Carbon::today()->startOfDay();
        $currentDay = new DateTime();
        $endOfDay = Carbon::today()->endOfDay();

        $Devices = $this->DeviceInfo();
        $AccessToken = $this->CreateToken();
        $Zoho_Credentials = $this->Zoho_Credentials();
        $SysConCommentUpdate = $this->SystemConfiguration()->where('Code_Name', 'Ticket_Timer_Comments')->first();

        CommentInTicketsJob::dispatch($startOfDay,$currentDay,$endOfDay,$Devices, $AccessToken,$Zoho_Credentials,$SysConCommentUpdate);
    }
    // public function CommentInTickets(){
       
    //     try{
    //     $startOfDay = Carbon::today()->startOfDay();
    //     $currentDay = new DateTime();
    //     $endOfDay = Carbon::today()->endOfDay();

    //     if($currentDay>=$startOfDay->copy()->addHours(8) && $currentDay<=$startOfDay->copy()->addMinutes(490)){
    //         $this->CommentInTicketsExtension();
    //     }else if($currentDay>=$startOfDay->copy()->addHours(16) && $currentDay<=$startOfDay->copy()->addMinutes(980)){
    //         $this->CommentInTicketsExtension();
    //     }else if($currentDay>=$startOfDay->copy()->addHours(22) && $currentDay<=$startOfDay->copy()->addHours(1330)){
    //         $this->CommentInTicketsExtension();
    //     }else{
    //         return response()->json([
    //             'Error' => "Not the time to comment",
    //         ]);
    //     }
    //         }catch(\Throwable $e){
    //             return response()->json([
    //                 'Error' => $e->getMessage(),
    //             ]);
                
    //         }
    // }
    // public function CommentInTicketsExtension(){
    //     try{
    //     $Devices = $this->DeviceInfo();
    //     $AccessToken = $this->CreateToken();
    //     $Zoho_Credentials = $this->Zoho_Credentials();
    //     $SysConCommentUpdate = $this->SystemConfiguration()->where('Code_Name', 'Ticket_Timer_Comments')->first();

    //     foreach($Devices as $Device){
    //         $Zoho_Ticket = DB::table('zoho_desks')
    //                         ->select('Ticket_Id','Ticket_Number','Device_Id')
    //                         ->where('Device_Id',$Device->Device_Id)
    //                         ->where('Status','Open')
    //                         ->first();
    
    //         if($Device->Status == 'Offline' && $Zoho_Ticket->Device_Id == $Device->Device_Id){
    //             $commentData = [
    //                 'isPublic' => false,
    //                 'attachmentIds' => null,
    //                 'contentType' => 'html',
    //                 'content' => ($SysConCommentUpdate->Code_Value), // Adjust as per your form or request data
    //             ];
    //             $client = new Client();
    //             $response = $client->post("https://desk.zoho.com/api/v1/tickets/{$Zoho_Ticket->Ticket_Id}/comments", [
    //                 'verify' => false,
    //                 'headers' => [
    //                     'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
    //                     'Content-Type' => 'application/json',
    //                     'orgId' => $Zoho_Credentials->orgID 
    //                 ],
    //                 'json' => $commentData,
    //             ]);
    //             $statusCode = $response->getStatusCode();
    //             $responseBody = json_decode($response->getBody()->getContents());
    //                     //assume it coommented on the ticket
    //             }
    //         }
    //     }catch(\Throwable $e){
    //         return response()->json([
    //             'Error' => $e->getMessage(),
    //         ]);
            
    //       }
    // }
    public function UpdateTickets(){
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
    }
    // public function UpdateTickets(){
    //     //System Update
        
    // try{  
    //     $startOfDay = Carbon::today()->startOfDay();
    //     $currentDay = new DateTime();
    //     $endOfDay = Carbon::today()->endOfDay();
    //    // $this->ExecUpdateTicket();
    //    if($currentDay>=$startOfDay->copy()->addHours(4) && $currentDay<=$startOfDay->copy()->addHours(5)){
    //     $this->ExecUpdateTicket();
    //         // dd("4am");
    //    }else if($currentDay>=$startOfDay->copy()->addHours(10) && $currentDay<=$startOfDay->copy()->addHours(12)){
    //         $this->ExecUpdateTicket();
    //         // dd("10am");
    //     }else if($currentDay>=$startOfDay->copy()->addHours(14) && $currentDay<=$startOfDay->copy()->addHours(16)){
    //         $this->ExecUpdateTicket();
    //         // dd("2pm");
    //     }else if($currentDay>=$startOfDay->copy()->addHours(22) && $currentDay<=$startOfDay->copy()->addHours(23)){
    //         $this->ExecUpdateTicket();
    //        // dd("11pm");
    //     }else{
    //         return response()->json([
    //             'Error' => "Not the time to update",
    //         ]);
    //     }
    //     //End of System Update
    //     }catch(\Throwable $e){
    //         return response()->json([
    //             'Error' => $e->getMessage(),
    //         ]);
            
    //     }
    // }
    // public function ExecUpdateTicket(){
    //     try{
    //         $array=[];
    //         $AccessToken = $this->CreateToken();
    //         $Zoho_Credentials = $this->Zoho_Credentials();
    //         $Offline_Devices = $this->DeviceInfo();
    //         $Device_Histories_CurrentYear = $this->DeviceHistory();
    //         $Count = $Device_Histories_CurrentYear->count();
    //         $Off_Devices =$this->Zoho_Desk_Open_Tickets();
        
    //         foreach($Off_Devices as $Off_Device){
    //             $lastRecord = DB::table('device_histories as a')
    //                             ->join('zoho_desks as b', 'a.Device_ID', '=', 'b.Device_Id')
    //                             ->join('devices as c','b.Device_ID', '=', 'c.Device_Id')
    //                             ->select('b.Ticket_Id','a.Device_ID','a.Device_Name','a.Device_Desc','a.Device_Loc','a.Room_Type','a.Manufacturer','a.Serial_Num','a.IP_Address','a.MAC_Add','a.Status','a.Previous_Date','a.created_at','a.updated_at')
    //                             ->where('a.Device_Id',$Off_Device->Device_Id)
    //                             ->where('b.Status','Open')
    //                             ->where('a.Status','Offline')
    //                             ->where('c.Status','Online')
    //                             ->latest('a.created_at')
    //                             ->first();
    //         if ($lastRecord !== null) {
    //             $dateStart = new DateTime($lastRecord->created_at);
    //             $dateEnd = new DateTime(); // Current date and time
    //             $interval = $dateEnd->getTimestamp() - $dateStart->getTimestamp();
    //             //86400 means one day or 24 hrs
    //             if(86400<=$interval){
    //             //The system will resolve the ticket itself
    //             $ticketId = $lastRecord->Ticket_Id;
    //                 $response = Http::withHeaders([
    //                     'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
    //                     'orgId' => $Zoho_Credentials->orgID,
    //                     'Content-Type' => 'application/json',
    //                 ])->patch("https://desk.zoho.com/api/v1/tickets/{$ticketId}", 
    //                 [ 
    //                 'statusType' => 'Closed',
    //                 'status' => 'Resolved by ESCO CARE 360',
    //                 ]);
    //                 $statusCode = $response->status();
    //                 $responseData = $response->json();

    //                 DB::table('zoho_desks')
    //                 ->where('Ticket_Id', $ticketId)
    //                 ->update(['Status' => 'Closed',
    //                         'Remarks' => 'Resolved By ESCO CARE 360',
    //                         'updated_at'=>now()]);
    //             }else{
    //             }
    //         }
    //         }
    //     }catch(\Throwable $e){
    //     return response()->json([
    //         'Error' => $e->getMessage(),
    //     ]);
    // }
    //    // dd($array);
    // }
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreZohoDeskRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ZohoDesk $zohoDesk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ZohoDesk $zohoDesk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateZohoDeskRequest $request, ZohoDesk $zohoDesk)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ZohoDesk $zohoDesk)
    {
        //
    }
}
