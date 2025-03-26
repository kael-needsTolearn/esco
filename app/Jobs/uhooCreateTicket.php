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

class uhooCreateTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }
    public function handle(): void
    {
        try{

            $Uhoo_Serial_Devices =$this->Uhoo_Serial_Devices();

            if($Uhoo_Serial_Devices){

                    $Zoho_Credentials = $this->Zoho_Credentials();
                    $Zoho_desk_Open_Tickets = $this->Zoho_Desk_Open_Tickets();
                    $SysCon = $this->SystemConfiguration();
                    $AccessToken = $this->CreateToken();
                    $SysConCompanyName= $SysCon->where('Code_Name', 'Company_Name')->first();
                    $SysConRemarks = $this->SystemConfiguration()->where('Code_Name','Ticket_Comment_Remarks')->first();
                    $SysConAccountManager = $this->SystemConfiguration()->where('Code_Name','Ticket_Account_Manager')->first();
                    $CompanyID = DB::table('user_accesses as a')
                                ->join('company_profile_details as b','a.Company_Id','=','b.Company_Id')
                                ->join('api_accounts as c','b.Api_Id','=','c.Api_Id')
                                ->select('a.User_Id','a.Company_Id')
                                ->where('User_Id',Auth::id())
                                ->where('Platform','uhoo')
                                ->get();
     
                foreach($Uhoo_Serial_Devices as $Uhoo)
                {
                    $uhooZohoDesk = DB::table('zoho_desks')
                    ->select('Ticket_Id')
                    ->where('Device_Id','=',$Uhoo->Serial_Number)
                    ->where('Status','=',$Uhoo->Label)
                    ->get();
                    if($uhooZohoDesk->isEmpty()){
                        $now = Carbon::now();
                        $format_updatedTime = Carbon::parse($Uhoo->Updated_At);
                        $difference = $now->diff($format_updatedTime);
                        $diffDays = $difference->days;
                        $diffHours = $difference->h;
                        $diffMinutes = $difference->i;
                
                        $diffAll = $difference->format('%d days, %h hours, %i minutes ago');
                        $dateNow = Carbon::now()->format('F d, Y g:ia');
                        $dateLast = $format_updatedTime->format('F d, Y g:ia');
                       // \Log::info('INFO:: ', ['info::' => $SysConCompanyName]);
                       $OpenTicketDevice = $Zoho_desk_Open_Tickets->where('Device_Id',$Uhoo->Serial_Number)->where('Subject',$Uhoo->Label)->first();
                        if($OpenTicketDevice){

                        }else{
                            $statusCode = $this->TicketData($Uhoo, $CompanyID,$SysConCompanyName,$dateNow,$dateLast,$diffAll,$SysConAccountManager,$AccessToken,$Zoho_Credentials,$SysConRemarks);
                        }
                    }
                
           
                }
                //\Log::info('INFO:: ', ['info::' => $arr]);
            }
        } catch (\Throwable $e) {
            \Log::error('Error in handle Creation of tickets:: ', ['exception' => $e->getMessage()]);
        }
    }

    private function TicketData($Uhoo, $CompanyID,$SysConCompanyName,$dateNow,$dateLast,$diffAll,$SysConAccountManager,$AccessToken,$Zoho_Credentials,$SysConRemarks)
    {
       
         $ticketData = [
            'subject' =>"Mac Address- ".$Uhoo->Mac_Address." - ". $Uhoo->Label . " - Condition: " . "\"".$Uhoo->Condition."\".", //Subject of the ticket
            'description' =>
            "Company Name: $SysConCompanyName->Code_Value<br><br>" .
                "<span>Description:<br>" .
                    "&nbsp;&nbsp;&nbsp;&nbsp;Serial Number: $Uhoo->Serial_Number<br>" .
                "&nbsp;&nbsp;&nbsp;&nbsp;Device Name: $Uhoo->Device_Name <br>" .
               // "&nbsp;&nbsp;&nbsp;&nbsp;MAC Address: $Uhoo->Mac_Address<br>" .
                 "&nbsp;&nbsp;&nbsp;&nbsp;Floor Number: $Uhoo->Floor_Number<br>" .
                "&nbsp;&nbsp;&nbsp;&nbsp;Room Name: $Uhoo->Room_Name<br>" .
                   "&nbsp;&nbsp;&nbsp;&nbsp;Label: $Uhoo->Label<br>" .
                "&nbsp;&nbsp;&nbsp;&nbsp;Condition: \" $Uhoo->Condition\"<br><br>" .
                 "Location: $Uhoo->Floor_Number<br>" .
                "Created Time: $dateNow <br> " .
                "Logged last Online: $dateLast <br> " .
                "Direct Link: "."<a href=".url('/uhooDashboard').">Click this link to redirect</a>",
            'statusType' => 'Open',
            'assigneeId' => null,
            'departmentId' => $Zoho_Credentials->departmentID, // ID of the department to which the ticket belongs
            'contactId' => $Zoho_Credentials->contactID, // Replace with the ID of the contact associated with the ticket / contact who raised the ticket.
            'priority' => null,
            'classification' => "Incident",
            'category' => "None",
            // 'email' => 'sample@example.com', // Replace with the email of the contact associated with the ticket
            'cf' => [
                // 'cf_requestor' => 'Test Requestor', // you can assign a value here if needed
                'cf_account_manager' => $SysConAccountManager->Code_Value, // you can assign a value here if needed
                'cf_billing_type' => "Billable", // you can assign a value here if needed
                'cf_sor_1' => 'TBA', // SOR
                'cf_region' => 'Philippines', // you can assign a value here if needed
                'cf_country_1' => "Philippines", // you can assign a value here if needed
                'cf_sub_category' => "Codec", // you can assign a value here if needed
                'cf_contract_type' => "AD HOC", // you can assign a value here if needed
                "cf_floor_1" => $Uhoo->Floor_Number,
                "cf_status_type" => "open",
                "cf_location_1" => $Uhoo->Room_Name,
            ],
            'customFields' => [
                "Contract Type" => "AD HOC",
                "Account Manager" => $SysConAccountManager->Code_Value,
                "SOR" => "TBA",
                "Billing Type" => "Billable",
                "Room Name/Room Number" => "Null",
                "Site Address" => "Philippines",
                "Region" => "Philippines",
                "Floor" => $Uhoo->Floor_Number,
            ]
        ];
        
        $client = new Client();
        $response = $client->post('https://desk.zoho.com/api/v1/tickets', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $AccessToken,
                'Content-Type' => 'application/json',
                'orgId' => $Zoho_Credentials->orgID // Add orgId to the headers
            ],
            'json' => $ticketData,
        ]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody()->getContents());
        if ($statusCode == 200) {

            $this->InsertToZoho($responseBody,$CompanyID,$Uhoo,$SysConRemarks,$dateLast,$diffAll);               
        }
    }
    private function InsertToZoho($responseBody,$CompanyID,$Uhoo,$SysConRemarks,$dateLast,$diffAll)
    {
      $inserted =  DB::table('zoho_desks')->insert([
            'Ticket_Id' => $responseBody->id, // Adjust column name as per your database table
            'Ticket_Number' => $responseBody->ticketNumber,
            'Company_Id' => $CompanyID,
            'Device_Id' => $Uhoo->Serial_Number,
            'Subject' =>$Uhoo->Label,
            'Status' => $Uhoo->Condition,
            'Remarks' => 'New',
            'Log_Last_Online' => $dateLast,
            'Elapse_Time' => $diffAll,
            'created_at' => now(),
            'updated_at' => null,
        ]);  
        \Log::info('INFO:: ', ['info::' => $inserted]);
    }
    private function Uhoo_Serial_Devices()
    {
        //return 
       return $Yellow = DB::table('uhoo_device_header as a')
                    ->join('uhoo_device_details as b', 'a.Serial_Number', '=', 'b.Serial_Number')
                    ->select('b.Label', 'b.Condition','b.Serial_Number','a.Mac_Address','b.Updated_At','a.Device_Name','a.Floor_Number','Room_Name')
                    ->whereIn('b.Condition', ['Moderate', 'Bad'])
                    ->where('b.Label', '!=', 'light')
                    ->get();
        
    }
    private function Zoho_Credentials()
    {
       
        return $Zoho_Credentials = DB::table('zoho_credentials')
                            ->select('code','clientID','clientSecret','access_token','refresh_token','orgID','departmentID','contactID')
                            ->first();
    }
    private function Zoho_Desk_Open_Tickets()
    {
        
        return $Zoho_desk_Open_Tickets = DB::table('zoho_desks')
                            ->select('Ticket_Id','Ticket_Number','Company_Id','Device_Id','Subject','Status','Remarks','Log_Last_Online','Elapse_Time')
                            ->whereIn('Status',['Moderate','Bad'])
                            ->get();
    }
    private function Zoho_Desk_Close_Tickets()
    {

        return $Zoho_desk_Close_Tickets = DB::table('zoho_desks')
                            ->select('Ticket_Id','Ticket_Number','Company_Id','Device_Id','Subject','Status','Remarks','Log_Last_Online','Elapse_Time')
                            ->where('Status','Closed')
                            ->get();
    }
    private function DeviceInfo()
    {

        return $Devices = DB::table('Devices as a')
                            ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                            ->select('Device_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','MAC_Address','Status','a.Api_Id','a.created_at','a.updated_at')
                            ->whereNotIn('Status',['Online','OK','Removed'])
                            ->distinct()
                            ->get();
    }
    private function DeviceRemovedStatus()
    {

        return $Devices = DB::table('Devices as a')
                            ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                            ->select('Device_Id','Company_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','MAC_Address','Status','a.Api_Id','a.created_at','a.updated_at')
                            ->where('Status','Removed')
                            ->get();
    }
    private function SystemConfiguration()
    {

        return $SysCon = DB::table('system_configurations')
                        ->select('Code_ID','Code_Name','Code_Value','Code_Description')
                        ->get();
    }   
    private function DeviceHistory()
    {

        $currentYear = Carbon::now()->year;
        $today = Carbon::today()->toDateString();

        return $DeviceHistory = DB::table('Device_histories as a')
                                ->join('zoho_desks as b', 'a.Device_ID', '=', 'b.Device_Id')
                                ->select('a.Device_ID','a.Device_Name','a.Device_Desc','a.Device_Loc','a.Room_Type','a.Manufacturer','a.Serial_Num','a.IP_Address','a.MAC_Add','a.Status','a.Previous_Date','a.created_at','a.updated_at')
                               ->whereDate('b.created_at', $today)
                                ->where('a.Status','Offline')
                                ->where('b.Status','Open')
                                ->get();
    }
    private function CreateToken(){
        try{
            $Zoho_Credentials = $this->Zoho_Credentials();

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
            $response = $client->post('https://accounts.zoho.com/oauth/v2/token', array_merge($accessTokenRequest, [
                'verify' => false,
            ]));
            $accessTokenData = json_decode($response->getBody()->getContents(), true);
            if(isset($accessTokenData['error'])){
                $accessTokenData = $this->recreateAccessToken();
                $Zoho_Credentials = DB::table('zoho_credentials')
                                    ->update(['code'=>$accessTokenData]);
            }else{
                $Zoho_Credentials = DB::table('zoho_credentials')
                                    ->update(['code'=>$accessTokenData['access_token'],
                                    'refresh_token'=>$accessTokenData['refresh_token']
                                    ]);
            }
            return $accessTokenData;
            }catch(\Throwable $e){
                return response()->json([
                'error'=>'function createToken Tickets job - '.$e->getMessage()
                ]);
            }
    }
    private function recreateAccessToken(){
        try{
            $Zoho_Credentials = $this->Zoho_Credentials();
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

            $Zoho_Credentials = DB::table('zoho_credentials')
            ->update(['code'=>$newAccessToken]);
            return $newAccessToken;
            }catch(\Throwable $e){
                return response()->json([
                'error'=>'function RecreateAccessToken TicketsJob - '.$e->getMessage()
                ]);
            }
    }
}
