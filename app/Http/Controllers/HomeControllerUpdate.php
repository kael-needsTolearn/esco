<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\DeviceRoom;
use App\Models\ZohoDesk;
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
use Illuminate\Support\Facades\Log;
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
class HomeControllerUpdate extends Controller
{
    protected $user;
    protected $ForgetPassData;
    protected $email;
    protected $authUser; 

    public function __construct(SystemConfig $SystemConfig)
    {
        // Initialize the authenticated user
        $this->authUser = Auth::user();
        $this->SystemConfig = $SystemConfig;
    }
    public function index(){
        $result = $this->SystemConfig->fetchconfiglogo();
        
        return redirect('/login');
    }
    public function resetPassword(request $request,$token){//after clicking reset password in the email
    
     try{
        $email =  User::where('remember_token', $request->token)->first();
        $mail = $email->email;
        $this->email = $mail;

            if($mail){
            $Date = DB::table('users')
                    ->select('updated_at')
                    ->where('email',$mail)
                    ->get();

            $UDate = \DateTime::createFromFormat('Y-m-d H:i:s', $Date[0]->updated_at);
            $currentDateTime = new \DateTime();
             $difference =  $currentDateTime->diff($UDate); //uncomment day computetaion 
             $daysDifference = $difference->days;
           //$difference =  $currentDateTime->diff($UDate);
           $minutesDifference = $difference->days * 24 * 60;
           $minutesDifference += $difference->h * 60; 
            $minutesDifference += $difference->i; 
            if ($minutesDifference  > 29) { //change $daysDifference
                // Return an error
                DB::table('users')
                ->where('email', $mail)
                ->update(['remember_token' => null]);
                Session::flush();
               abort(401, 'Link Expired');
            } else {
                session(['mail' => $mail]);
                return view('auth.forgot-password');
            }
           
        }else{
            //return view('auth.forgot-password');
           
        }
     }catch(\Throwable $e){
        abort(401, 'Link Expired');
     }
 
    }
    public function EmailMessage(){
        return view('auth.EmailMessage');
    }
    public function sendNotification(User $user,Request $request){ //creation of email
        $email = $request->emailaddress;
        //Crypt::encryptString($request->emailaddress);     
        $ResetUserToken = DB::table('users')
                            ->where('email',$email)
                            ->update([
                                'remember_token'=>null,
				                'updated_at'=> now(),
                            ]);
        $token = Str::random(40);

        $DBEmail = DB::table('users')
             ->where('email', $email)
             ->first();
        $InsertUserToken = DB::table('users')
                            ->where('email',$email)
                            ->update([
                                'remember_token'=>$token,
 				                'updated_at'=> now(),
                            ]);

        $user = User::where('email', $email)->first();

        if($DBEmail){
        // $user->update(['remember_token' => null]);
        // $user->remember_token = $token;
        // $user->save();
        $ForgetPassData = [
            'body'=>'You request to change your password, click "Reset Password" to proceed.',
            'subject'=>'Reset Password',
            'url' =>  url('resetPassword'.$token),
            'ThankyouMessage'=>'Contact escocare360@esco.com.ph for further assistance.',
            'email'=>$user->email,
            'token'=>$token
        ];
        LoginNotifJob::dispatch($user,$ForgetPassData,2);
     
        }else{
            return response()->json([
                'message' => 'There is no existing email address in the database.'
            ]);
        }
    }
    public function SaveResetPassword(Request $request){
        //$sess=session('gForgetPassData');
       // $email = $sess['email'];
        //$email = $request->emailaddress;
        $pass = $request->newpassword;
        $newpass = $request->confirmpassword;

        $data =  User::where('email', session('mail'))->first();
   
        if ($data) {
            if($pass == $newpass){
            $data->email = session('mail');
            $data->password = Hash::make($pass);
            $data->save();
            LogJob::dispatch($this->authUser,'Updated the users password');

            DB::table('users')
                ->where('email', session('mail'))
                ->update(['remember_token' => null]);
            Session::flush();
            }else{
                return response()->json([
                    'AlertNotif' => 'Password do not match',
                ]);
            }
        }else{
            return response()->json([
                'AlertNotif' => "No email found",
            ]);
        }

       
    }
    public function dashboard(){
      try{
        $auth = $this->authUser();
        $Display = $this->dashboardDisplay();
        $uptimeData = $this->uptimeDevice();
        $ave = $this->ave($uptimeData);
        //$TimeOutSession = $this->timeoutSession();
        return view('admin.dashboard.home',
                ['CompanyProfiles'=>$Display['CompanyProfiles'],
                'DeviceRoom'=>$Display['Rooms'],
                'NewNotif'=>$Display['NewNotif'],
                'ResolvedNotif'=>$Display['ResolvedNotif'],
                'Devices'=>$Display['Devices'],
                'data' => $uptimeData,
                'ave' =>number_format($ave, 3),
                'UnresolvedNotif'=>$Display['UnresolvedNotif']
                ]);
            
        } catch (\Throwable $e) {
            Auth::guard('web')->logout();
            abort(401, 'No User Access ask for the admin');
            Session::flush();
        }
    }
    public function version2(){ //update devices
         Version2::dispatch(auth()->id());
        \Log::info('Version2 job dispatched.');
       // SimpleJob::dispatch(auth()->id());
    }
    public function refreshDevice(){
        $ParentOfTheRooms = [];
        $auth = $this->authUser();
        $apis = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.user_Id')
                        ->join('company_profiles as c','c.Company_Id','=','b.Company_Id')
                        ->join('company_profile_details as d','d.Company_Id','=','c.Company_Id')
                        ->join('api_accounts as e','e.Api_Id','=','d.Api_Id')
                        ->select('e.Api_Id','e.Platform','e.Variable1','e.Variable2','e.Variable3')
                        ->where('a.id',$auth->id)
                        ->get();
        \Log::info('refreshDevice Rooms APIS!!!!.',(['Api'=>$apis]));       
        foreach ($apis as $api) {
            if ($api->Platform == 'xio') {
                \Log::info('Went here XIO API',(['Api'=>$apis]));  
                $subscriptionKey = Crypt::decryptString($api->Variable1);
                //'4c86c8b07c6a4a29a6fe580209f45352';
                // 
                $accountId =Crypt::decryptString($api->Variable2);
                //'4bed681e-cf04-4929-87d0-c135b8c3b7a9';
                // 
                //Insert rooms first
                    $room_url = "https://api.crestron.io/api/v1/group/accountid/{$accountId}/groups";  //Account Groups to get group id of isRoom = true
                    $responseRoom = Http::withHeaders([
                        'XiO-subscription-key' => $subscriptionKey,
                    ])->withOptions([
                        'verify' => false // Disable SSL verification
                    ])->get($room_url);
                    if ($responseRoom->successful()) {
                        $rooms = $responseRoom->json();
                        $parentRooms = [];
                        // Collect parent rooms
                        foreach ($rooms as $room) {
                            if (!$room['IsRoom']) {
                                $parentRooms[$room['id']] = $room['Name'];
                            }
                        }
                        // Process each room
                        foreach ($rooms as $room) {
                            if ($room['IsRoom']) {
                                $existingRoom = DeviceRoom::where('DeviceRoomID', $room['id'])->first();
                                // Create new room if it doesn't exist
                                if (!$existingRoom) {
                                    $deviceRoom = new DeviceRoom();
                                    $deviceRoom->Api_Id = $api->Api_Id;
                                    $deviceRoom->DeviceRoomID = $room['id'];
                                    $deviceRoom->DeviceRoomName = $room['Name'];
                                    // Set room location if parent room exists
                                    $deviceRoom->DeviceRoomLocation = $parentRooms[$room['ParentGroupId']] ?? "";
                                    $deviceRoom->save();
                                }
                            }
                        }
                    }else{
                        \Log::info('refreshDevice Rooms false.',(['Api'=>$responseRoom->json()]));
                        continue; 
                    }
            }  
        }
        RefreshDeviceJob::dispatch(auth()->id());
        \Log::info('refreshDevice job dispatched.');
    }
    public function refreshNotification(){
        $Display = $this->dashboardDisplay();
        
        return response()->json([
            'AvailableRooms' => $Display['Rooms'],  
            'NewNotif' => $Display['NewNotif'],  
            'ResolvedNotif'=>$Display['ResolvedNotif'],
            'UnresolvedNotif'=>$Display['UnresolvedNotif']
        ]);
       
    }
    public function filterNotification(Request $request){
        $date_range =  $request->initialDateRange;
        [$start_date, $end_date] = explode(' - ', $date_range);
        $start_date = Carbon::createFromFormat('m/d/Y', $start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('m/d/Y', $end_date)->endOfDay();
        $user = $this->authUser();
            $NewNotif = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                ->select(DB::raw('count(*) as new_count')) // Alias the count result for easier access
                ->whereIn('c.Status', ['Open','new'])
                ->whereBetween('c.created_at', [$start_date, $end_date])
                ->whereDate('c.created_at', Carbon::today()) 
                ->where('a.id', $user->id)
                ->first();
            $ResolvedNotif = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                ->select(DB::raw('count(*) as resolved_count')) // Alias the count result for easier access
                ->where('c.Status', 'Closed')
                ->whereBetween('c.created_at', [$start_date, $end_date])
                ->where('a.id', $user->id)
                ->first();
            $UnresolvedNotif = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                ->select(DB::raw('count(*) as unresolved_count')) // Alias the count result for easier access
                ->where('c.Status', 'Open')
                ->whereBetween('c.created_at', [$start_date, $end_date])
                ->where('a.id', $user->id)
                ->first();

                return response()->json([
                    'NewNotif' => $NewNotif,
                    'ResolvedNotif' => $ResolvedNotif,
                    'UnresolvedNotif' => $UnresolvedNotif
                ]);
    }
    public function refreshDeviceWithParam(Request $request){
        try{
            $user = Auth::user();
        
                $RoomIds = $request->SelValues;
                $Country = $request->gCountry;
                $OrgId = $request->gOrgId;
                $Devices = DB::table('devices')
                    ->Leftjoin('device_rooms', 'devices.DeviceRoomID', '=', 'device_rooms.DeviceRoomID')
                    ->select('devices.*', 'device_rooms.DeviceRoomLocation')
                    ->whereIn('devices.DeviceRoomID', $RoomIds)
                    ->where('devices.status', '!=', 'removed') 
                    ->orderBy('Device_Name', 'asc')
                    ->get();

                $Company = DB::table('company_profiles')
                    ->select('company_profiles.*')
                    ->where('Company_Id', '=', $request->gOrgId)
                    ->get();

                $CountDevices = DB::table('devices')
                    ->select(
                        DB::raw('SUM(CASE WHEN Status = "Online" THEN 1 ELSE 0 END) AS online_count'),
                        DB::raw('SUM(CASE WHEN Status = "Offline" THEN 1 ELSE 0 END) AS offline_count')
                    )
                    ->whereIn('DeviceRoomID', $RoomIds)
                    ->get();

                $NotificationSummary = DB::table('zoho_desks')
                ->select(DB::raw('count(*) as Ticket_Count'))
                ->where('Status', '=', 'Open')
                ->where('Company_Id','=',$OrgId)
                ->get();

                return response()->json([
                    'Devices' => $Devices,
                    'DeviceStatus' => $Company,
                    'NotificationSumm' => $NotificationSummary,
                    'DeviceOfflineIncidets' => $CountDevices,
                    // 'online' => $online,
                    // 'offline' => $offline
                ]);
            }catch(\Throwable $e){
                return response()->json([
                    'Error'=>'function refreshDeviceWithParam - '.$e->getMessage()
                ]);
            }
    }
    public function InitReports(Request $request){
        $region = $this->region();
        $devices = $this->devices();
        
        return view('admin.dashboard.reports', compact('devices','region'));
    }
        private function region(){
            $Auth = $this->authUser();
            return  DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profiles as c','c.Company_Id','=','b.Company_Id')
                    ->select('c.Company_Id','c.Company_Name','c.Company_Address','c.Country','c.Contract_Name')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
        }
    public function FilterByRegion(Request $request){//Onchange ng Region sa reports{
        try{
            $AuthUser = $this->authUser();
            $DisplayOrganization = $this->organization($request->Region);
            $DisplayReports = $this->reportAnalytics($request->Region);
            $DisplayZoho = $this->zohoTickets($request->Region);
            $TicketStatus = $this->ticketStatus($request->Region);
            $ResolutionTime = $this->resolutionTime($request->Region);
            return response()->json([
                'AuthUser' => $AuthUser,
                'org' => $DisplayOrganization,
                'report' => $DisplayReports,
                'DisplayZoho' =>$DisplayZoho,
                'TicketStatus' =>$TicketStatus,
                'ResolutionTime' =>$ResolutionTime,
            ]);
        }catch(\Throwable $e){
            return response()->json([
                'Error'=>'function FilterByRegion - '.$e->getMessage()
            ]);
        }
    }
        private function resolutionTime($region){
            $Auth = $this->authUser();
            $ZohoTickets =  DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                        ->join('zoho_desks as f','f.Device_Id','=','d.Device_Id')
                        ->select('f.*','d.Device_Name')
                        ->where('a.id',$Auth->id)
                        ->where('f.Status','Closed')
                        ->where('e.Country',$region)
                        ->distinct()
                        ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                        ->get();
            $resolution = [];
            foreach ($ZohoTickets as $ZohoTickets) {
                if($ZohoTickets->updated_at!=null){
                $startDate = Carbon::parse($ZohoTickets->created_at); // May 31, 2024, 11:54 am
                $endDate = Carbon::parse($ZohoTickets->updated_at); // Current date and time
                $startDate->format('Y-m-d H:i:s');
                $endDate->format('Y-m-d H:i:s');
             
                $diff = $endDate->diff($startDate);
                $totalDays = $diff->d;
                $totalHours = $diff->h;
                $totalMinutes = $diff->i;
                $resolution [] = $totalDays.' day(s) '.$totalHours.' hr(s) '.$totalMinutes.' minute(s)';
                }
            }
            return $resolution;
        }
        private function ticketStatus($region){
            $Auth = $this->authUser();
            return DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                        ->join('zoho_desks as f','f.Device_Id','=','d.Device_Id')
                        ->select('f.Status')
                        ->where('a.id',$Auth->id)
                        ->where('e.Country',$region)
                        ->distinct()
                        ->get();
        }
        private function zohoTickets($region){
            $Auth = $this->authUser();
            return DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                        ->join('zoho_desks as f','f.Device_Id','=','d.Device_Id')
                        ->select('f.*','d.Device_Name')
                        ->where('a.id',$Auth->id)
                        ->where('e.Country',$region)
                        ->distinct()
                        ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                        ->get();
        }
        private function reportAnalytics($region){
             $Auth = $this->authUser();
            return DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                        ->select('d.*')
                        ->where('a.id',$Auth->id)
                        ->where('e.Country',$region)
                        ->where('d.Status','!=','Removed')
                        ->distinct()
                        ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                        ->get();
        }
        private function organization($region){
            $Auth = $this->authUser();
            return DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profiles as c','b.Company_Id','=','c.Company_Id')
                        ->select('c.*')
                        ->where('a.id',$Auth->id)
                        ->where('c.Country',$region)
                        ->distinct()
                        ->get();
        }
        public function FilterByOrganization(Request $request){
            $Auth = $this->authUser();
            $Country = $request->gRegion;
            $CompanyId = $request->Organization;//This is company ID
            $Devices = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','c.Company_Id','=','b.Company_Id')
                        ->join('company_profiles as e','c.Company_Id','=','e.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->select('d.Device_Id','d.Device_Name','d.DeviceRoomID','d.Device_Desc','d.Device_Loc','d.Room_Type','d.Manufacturer','d.Serial_Number','d.IP_Address','d.Mac_Address','d.Status','d.Api_Id')
                        ->where('a.id',$Auth->id)
                        ->where('e.Country',$Country)
                        ->where('e.Company_Id',$CompanyId)
                        ->where('d.Status','!=','Removed')
                        ->distinct()
                        ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                        ->get();
            $ZohoTickets = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                        ->join('zoho_desks as f','f.Device_Id','=','d.Device_Id')
                        ->select('f.*','d.Device_Name')
                        ->where('a.id',$Auth->id)
                        ->where('e.Country',$Country)
                        ->where('e.Company_Id',$CompanyId)
                        ->distinct()
                        ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                        ->get();
            $DropdownStatus = DB::table('users as a')
                            ->join('user_accesses as b','a.id','=','b.User_Id')
                            ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                            ->join('devices as d','d.Api_Id','=','c.Api_Id')
                            ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                            ->join('zoho_desks as f','f.Device_Id','=','d.Device_Id')
                            ->select('f.Status')
                            ->where('a.id',$Auth->id)
                            ->where('e.Country',$Country)
                            ->where('e.Company_Id',$CompanyId)
                            ->distinct()
                            ->orderBy('d.Device_Name') // Add this line to sort by Device_Name
                            ->get();
            return response()->json([
                'Devices' => $Devices,
                'ZohoTickets' => $ZohoTickets,
                'DropdownStatus' => $DropdownStatus,
            ]);
    }
    public function AlertNotification(){
        $user = Auth::user();
        try{

        //UPDATE TABLE FROM DATABASE Elapse time
        $Zoho_Desk = DB::table("zoho_desks")
                        ->SELECT("Ticket_Number","Ticket_Id","Log_Last_Online","Elapse_Time")
                        ->get();

        foreach ($Zoho_Desk as $Zoho_Desks) {
        $startDate = Carbon::parse($Zoho_Desks->Log_Last_Online); // May 31, 2024, 11:54 am
        $endDate = Carbon::now(); // Current date and time
        $startDate->format('Y-m-d H:i:s');
        $endDate->format('Y-m-d H:i:s');
        // Calculate the difference
        $diff = $endDate->diff($startDate);
    
        // Access the difference in days, hours, and minutes
        $totalDays = $diff->d;
        $totalHours = $diff->h;
        $totalMinutes = $diff->i;
        //DB::table('zoho_desks')->where('Ticket_Id', $Zoho_Desks->Ticket_Id)->update(['Elapse_Time' => $totalDays.'days'.$totalHours.'hrs'.$totalMinutes.'minutes']);
        DB::table('zoho_desks')
        ->where('Ticket_Id', $Zoho_Desks->Ticket_Id)
        ->where('Status','Open')
        ->update(['Elapse_Time' => $totalDays.' days '.$totalHours.' hrs '.$totalMinutes.' minutes']);  
        // $Zoho_Desks->update(['Elapse_Time' => '000hrs']);
        }
            // if ($Organization) {
            //     $AlertNotif = DB::table('zoho_desks')
            //         ->select('zoho_desks.*', 'company_profiles.Country', 'devices.Device_Name', DB::raw('DATE_FORMAT(zoho_desks.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(zoho_desks.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
            //         ->join('company_profiles', 'zoho_desks.Company_Id', '=', 'company_profiles.Company_Id')
            //         ->join('devices', 'devices.Device_Id', '=', 'zoho_desks.Device_Id')
            //     // ->where('zoho_desks.Status', '=', 'Open')
            //         ->where('Country', '=', $Region)
            //         ->where('company_profiles.Company_Id', '=', $Organization)
            //         ->get();
            // } else {
            //     $AlertNotif = DB::table('zoho_desks')
            //         ->select('zoho_desks.*', 'company_profiles.Country', 'devices.Device_Name', DB::raw('DATE_FORMAT(zoho_desks.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(zoho_desks.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
            //         ->join('company_profiles', 'zoho_desks.Company_Id', '=', 'company_profiles.Company_Id')
            //         ->join('devices', 'devices.Device_Id', '=', 'zoho_desks.Device_Id')
            //     // ->where('zoho_desks.Status', '=', 'Open')
            //         ->where('Country', '=', $Region)
            //         ->get();
            // }

            // $TicketStatus = DB::table('zoho_desks')
            //                 ->select('Status')
            //                 ->distinct()
            //                 ->get();     
            // return response()->json([
            //     'AlertNotif' => $AlertNotif,
            //   //  'TicketStatus' => $TicketStatus,

            // ]);
            }catch(\Throwable $e){
                return response()->json([
                    'Error'=>'function AlertNotification - '.$e->getMessage()
                ]);
            }
        
    }
    public function ChangeTicketStatus(Request $request){
        $user = Auth::user();
        if($user->usertype==2){
            $DevicesOfUser = DB::table('user_accesses as a')
            ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
            ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
            ->join('devices as d','d.Device_Id','=','b.Device_Id')
            ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
            ->where('b.Status',$request->status)
            ->where('c.Country',$request->gRegion)
            ->distinct()
            ->get();
        return response()->json([
            'DevicesOfUser' => $DevicesOfUser,
        ]);
        }else{
        $DevicesOfUser = DB::table('user_accesses as a')
                        ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
                        ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Device_Id','=','b.Device_Id')
                        ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                        ->where('b.Status',$request->status)
                        ->where('c.Country',$request->gRegion)
                        ->where('a.User_Id',$request->gUserId)
                        ->distinct()
                        ->get();
                    return response()->json([
                        'DevicesOfUser' => $DevicesOfUser,
                    ]);
        }
    }
    public function ReportsChangeDate(Request $request){
        $dateRangeArray = explode(' - ', $request->AlertDateSelected);
        $startDate = Carbon::createFromFormat('m/d/Y', $dateRangeArray[0])->startOfDay()->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', $dateRangeArray[1])->endOfDay()->format('Y-m-d');
    
        $user = Auth::user();
        if($user->usertype == 2){
            if($request->gStatus === null){
                $TicketsByDate = DB::table("user_accesses as a")
                                ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
                                ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
                                ->join('devices as d','d.Device_Id','=','b.Device_Id')
                                ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),'d.Device_Name',DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                                ->where('c.Country',$request->gRegion)
                                ->whereDate('b.created_at', '>=', $startDate)
                                ->whereDate('b.created_at', '<=', $endDate)
                                ->distinct()
                                ->get();
        
                            return response()->json([
                                'TicketsByDate' => $TicketsByDate,
                            ]);
                }else{
                    $TicketsByDate = DB::table("user_accesses as a")
                    ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
                    ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
                    ->join('devices as d','d.Device_Id','=','b.Device_Id')
                    ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),'d.Device_Name',DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                    ->where('c.Country',$request->gRegion)
                    ->where('b.Status',$request->gStatus)
                    ->whereDate('b.created_at', '>=', $startDate)
                    ->whereDate('b.created_at', '<=', $endDate)
                    ->distinct()
                    ->get();
        
                    return response()->json([
                        'TicketsByDate' => $TicketsByDate,
                    ]);
                }
        }else{
        if($request->gStatus === null){
        $TicketsByDate = DB::table("user_accesses as a")
                        ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
                        ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Device_Id','=','b.Device_Id')
                        ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),'d.Device_Name',DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                        ->where('a.User_Id',$request->gUserId)
                        ->where('c.Country',$request->gRegion)
                        //->where('b.Status',$request->status)
                        ->whereDate('b.created_at', '>=', $startDate)
                        ->whereDate('b.created_at', '<=', $endDate)
                        ->distinct()
                        ->get();

                    return response()->json([
                        'TicketsByDate' => $TicketsByDate,
                    ]);
        }else{
            $TicketsByDate = DB::table("user_accesses as a")
            ->join('zoho_desks as b','a.Company_Id', '=', 'b.Company_Id')
            ->join('company_profiles as c', 'b.Company_Id','=','c.Company_Id')
            ->join('devices as d','d.Device_Id','=','b.Device_Id')
            ->select('b.*','d.Device_Name',DB::raw('DATE_FORMAT(b.created_at, "%M %d, %Y %l:%i%p") as created_at'),'d.Device_Name',DB::raw("DATE_FORMAT(b.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
            ->where('a.User_Id',$request->gUserId)
            ->where('b.Status',$request->gStatus)
            ->where('c.Country',$request->gRegion)
            ->whereDate('b.created_at', '>=', $startDate)
            ->whereDate('b.created_at', '<=', $endDate)
            ->distinct()
            ->get();

            return response()->json([
                'TicketsByDate' => $TicketsByDate,
            ]);
        }
        }
    }
    public function UpdateField(Request $request){

        $oldData = DB::table('devices')
                    ->select($request->gColumn)
                    ->where('Device_Id', $request->gDevId)
                    ->first();
        LogJob::dispatch($this->authUser,'Updated device '.$request->gDevId.' - '. $request->gColumn.' from '.$oldData->{$request->gColumn}.' to '.$request->Inputval.' ');
       
        DB::table('devices')
        ->where('Device_Id', $request->gDevId)
        ->update([$request->gColumn => $request->Inputval ,
                'updated_at'=>now()]);
    }
    public function AscDesc(Request $request){
        $Region = $request->gRegion;
        $UserId =  $request->gUserId;
        $Organization = $request->gOrganization;
        $Column = $request->val;
        $ctr = $request->gCtr;
        $Devices = null;
        $DevicesDes = null;
        $Auth = $this->authUser();
        if ($UserId) {
            if ($ctr == 1) {
            
                // $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                //     ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                //     ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                //     ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                //     ->where('user_accesses.User_Id', $UserId)
                //     ->where('company_profiles.Country', $Region)
                //     ->where('devices.Status','!=','Removed')
                //     ->select('devices.*')
                //     ->orderBy("devices.$Column", 'asc') // Order by ascending device location
                //     ->distinct()
                //     ->get();
             
                $Devices = DB::table('users as a')
                            ->join('user_accesses as b','a.id','=','b.User_Id')
                            ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                            ->join('devices as d','d.Api_Id','=','c.Api_Id')
                            ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                            ->select('d.*')
                            ->where('a.id',$Auth->id)
                            ->where('e.Country',$Region)
                            ->where('d.Status','!=','Removed')
                            ->distinct()
                            ->orderBy("d.$Column", 'asc') // Add this line to sort by Device_Name
                            ->get();
                // $this->reportAnalytics($request->Region)->orderBy("devices.$Column", 'asc')->get();
           
            } else {
                //order by descending 
                // $DevicesDes = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                //     ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                //     ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                //     ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                //     ->where('user_accesses.User_Id', $UserId)
                //     ->where('company_profiles.Country', $Region)
                //     ->where('devices.Status','!=','Removed')
                //     ->select('devices.*')
                //     ->orderBy("devices.$Column", 'desc') // Order by ascending device location
                //     ->distinct()
                //     ->get();
                    $DevicesDes =  DB::table('users as a')
                                ->join('user_accesses as b','a.id','=','b.User_Id')
                                ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                                ->join('devices as d','d.Api_Id','=','c.Api_Id')
                                ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                                ->select('d.*')
                                ->where('a.id',$Auth->id)
                                ->where('e.Country',$Region)
                                ->where('d.Status','!=','Removed')
                                ->distinct()
                                ->orderBy("d.$Column", 'desc') // Add this line to sort by Device_Name
                                ->get();
            }
        } else {
            if ($ctr == 1) {
                // $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                //     ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                //     ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                //     ->where('company_profiles.Country', $Region)
                //     ->where('devices.Status','!=','Removed')
                //     ->select('devices.*')
                //     ->orderBy("devices.$Column", 'asc') // Order by ascending device location
                //     ->distinct()
                //     ->get();
                $Devices = DB::table('users as a')
                            ->join('user_accesses as b','a.id','=','b.User_Id')
                            ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                            ->join('devices as d','d.Api_Id','=','c.Api_Id')
                            ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                            ->select('d.*')
                            ->where('a.id',$Auth->id)
                            ->where('e.Country',$Region)
                            ->where('d.Status','!=','Removed')
                            ->distinct()
                            ->orderBy("d.$Column", 'asc') // Add this line to sort by Device_Name
                            ->get();

            } else {
                //order by descending 
                // $DevicesDes = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                //     ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                //     ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                //     ->where('company_profiles.Country', $Region)
                //     ->where('devices.Status','!=','Removed')
                //     ->select('devices.*')
                //     ->orderBy("devices.$Column", 'desc') // Order by ascending device location
                //     ->distinct()
                //     ->get();
                $DevicesDes =  DB::table('users as a')
                                ->join('user_accesses as b','a.id','=','b.User_Id')
                                ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                                ->join('devices as d','d.Api_Id','=','c.Api_Id')
                                ->join('company_profiles as e','e.Company_Id','=','c.Company_Id')
                                ->select('d.*')
                                ->where('a.id',$Auth->id)
                                ->where('e.Country',$Region)
                                ->where('d.Status','!=','Removed')
                                ->distinct()
                                ->orderBy("d.$Column", 'desc') // Add this line to sort by Device_Name
                                ->get();
            }
        }



        return response()->json([
            'asc' => $Devices,
            'desc' => $DevicesDes,


        ]);
    }
    public function SearchDevice(Request $request)
    {
        $var = $request->value;

        if ($request->gUserId == null) { //admin
            if ($request->gOrganization) {
                $result = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->where('company_profiles.Country', $request->gRegion)
                    ->where('company_profiles.Company_Id', $request->gOrganization)
                    ->when(isset($request->gOrganization), function ($query) use ($request) {
                        return $query->where('company_profiles.Company_Id', $request->gOrganization);
                    })
                    ->whereAny(
                        [
                            'devices.Device_Name',
                            'devices.Device_Loc',
                            'devices.Device_Desc',
                            'devices.Room_Type',
                            'devices.Manufacturer',
                            'devices.Serial_Number',
                            'devices.Mac_Address',
                            'devices.Status',

                        ],
                        'like',
                        "%{$var}%"
                    )
                    ->select('devices.*')
                    ->distinct()
                    ->get();
            } else {
                $result = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->where('company_profiles.Country', $request->gRegion)
                    ->when(isset($request->gOrganization), function ($query) use ($request) {
                        return $query->where('company_profiles.Company_Name', $request->gOrganization);
                    })
                    ->whereAny(
                        [
                            'devices.Device_Name',
                            'devices.Device_Loc',
                            'devices.Device_Desc',
                            'devices.Room_Type',
                            'devices.Manufacturer',
                            'devices.Serial_Number',
                            'devices.Mac_Address',
                            'devices.Status',

                        ],
                        'like',
                        "%{$var}%"
                    )
                    ->select('devices.*')
                    ->distinct()
                    ->get();
            }
        } else {
            $result = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                ->where('user_accesses.User_Id', $request->gUserId)
                ->where('company_profiles.Country', $request->gRegion)
                ->when(isset($request->gOrganization), function ($query) use ($request) {
                    return $query->where('company_profiles.Company_Id', $request->gOrganization);
                })
                ->whereAny(
                    [
                        'devices.Device_Name',
                        'devices.Device_Loc',
                        'devices.Device_Desc',
                        'devices.Room_Type',
                        'devices.Manufacturer',
                        'devices.Serial_Number',
                        'devices.Mac_Address',
                        'devices.Status',
                    ],
                    'like',
                    "%{$var}%"
                )
                ->select('devices.*')
                ->distinct()
                ->get();
        }

        return response()->json([
            'results' => $result,

        ]);
    }
    public function DeviceStatus(Request $request){
    
            $rooms = $request->checkedRooms;
            $allOnline = 0;
            $allOffline = 0;
            $allOK = 0;
            $allMissing = 0;
            $allUnknown = 0;
            $allInitializing = 0;
            $allCompromised = 0;
            $allFault = 0;
            $allNotPresent = 0;
            if($rooms){
                foreach ($rooms as $room) {
                    $on = Device::where('DeviceRoomID', $room)->whereIn('status', ['online'])->count();
                    $off = Device::where('DeviceRoomID', $room)->whereIn('status', ['Offline'])->count();
                    $OK = Device::where('DeviceRoomID', $room)->whereIn('status', ['OK'])->count();
                    $Missing = Device::where('DeviceRoomID', $room)->whereIn('status', ['Missing'])->count();
                    $Unknown = Device::where('DeviceRoomID', $room)->whereIn('status', ['Unknown'])->count();
                    $Initializing = Device::where('DeviceRoomID', $room)->whereIn('status', ['Initializing'])->count();
                    $Compromised = Device::where('DeviceRoomID', $room)->whereIn('status', ['Compromised'])->count();
                    $Fault = Device::where('DeviceRoomID', $room)->whereIn('status', ['Fault'])->count();
                    $NotPresent = Device::where('DeviceRoomID', $room)->whereIn('status', ['Not Present','NotPresent','Not_Present'])->count();
    
                    $allOnline += $on;
                    $allOffline += $off;
                    $allOK += $OK;
                    $allMissing += $Missing;
                    $allUnknown += $Unknown;
                    $allInitializing += $Initializing;
                    $allCompromised += $Compromised;
                    $allFault += $Fault;
                    $allNotPresent += $NotPresent;
                }
                return response()->json([
                    'online' => $allOnline,
                    'offline' => $allOffline,
                    'OK' => $allOK,
                    'Missing' => $allMissing,
                    'Unknown' => $allUnknown,
                    'Initializing' => $allInitializing,
                    'Compromised' => $allCompromised,
                    'Fault' => $allFault,
                    'NotPresent' => $allNotPresent,
                ]);
            }else{
                return response()->json([
                    'error' => 'fetch rooms first'
                ]);
            }
                
    }
    public function rooms(Request $request){ //Device Status Display
    
        $Auth = $this->authUser();
        $rooms = $request->checkedRooms;
        $allOnline = 0;
        $allOffline = 0;
        $allOK = 0;
        $allMissing = 0;
        $allUnknown = 0;
        $allInitializing = 0;
        $allCompromised = 0;
        $allFault = 0;
        $allNotPresent = 0;
        $devices = [];
        // Check if $rooms is empty
        if (!empty($rooms)) {
            $devicesRooms = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                        ->join('devices as d','d.Api_Id','=','c.Api_Id')
                        ->join('device_rooms as e','e.DeviceRoomID','=','d.DeviceRoomID')
                        ->select('d.*','e.DeviceRoomLocation')
                        ->where('a.id',$Auth->id)
                        ->whereIn('d.DeviceRoomID', $rooms)
                        ->where('d.Status', '!=', 'removed')
                        ->distinct()
                        ->get();
            
            foreach ($rooms as $room) {
                $on = Device::where('DeviceRoomID', $room)->whereIn('status', ['online'])->count();
                $off = Device::where('DeviceRoomID', $room)->whereIn('status', ['Offline'])->count();
                $OK = Device::where('DeviceRoomID', $room)->whereIn('status', ['OK'])->count();
                $Missing = Device::where('DeviceRoomID', $room)->whereIn('status', ['Missing'])->count();
                $Unknown = Device::where('DeviceRoomID', $room)->whereIn('status', ['Unknown'])->count();
                $Initializing = Device::where('DeviceRoomID', $room)->whereIn('status', ['Initializing'])->count();
                $Compromised = Device::where('DeviceRoomID', $room)->whereIn('status', ['Compromised'])->count();
                $Fault = Device::where('DeviceRoomID', $room)->whereIn('status', ['Fault'])->count();
                $NotPresent = Device::where('DeviceRoomID', $room)->whereIn('status', ['Not Present','NotPresent','Not_Present'])->count();

                $allOnline += $on;
                $allOffline += $off;
                $allOK += $OK;
                $allMissing += $Missing;
                $allUnknown += $Unknown;
                $allInitializing += $Initializing;
                $allCompromised += $Compromised;
                $allFault += $Fault;
                $allNotPresent += $NotPresent;
            }
            
        } 

        $NewNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                        ->select(DB::raw('count(*) as new_count')) // Alias the count result for easier access
                        ->whereIn('c.Status', ['Open','new'])
                        ->whereDate('c.created_at', Carbon::today()) 
                        ->where('a.id', $Auth->id)
                        ->first();
        $ResolvedNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                        ->select(DB::raw('count(*) as resolved_count')) // Alias the count result for easier access
                        ->where('c.Status', 'Closed')
                        ->where('a.id', $Auth->id)
                        ->first();
        $UnresolvedNotif = DB::table('users as a')
                            ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                            ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                            ->select(DB::raw('count(*) as unresolved_count')) // Alias the count result for easier access
                            ->where('c.Status', 'Open')
                            ->where('a.id', $Auth->id)
                            ->first();
        return response()->json([
            'online' => $allOnline,
            'offline' => $allOffline,
            'OK' => $allOK,
            'Missing' => $allMissing,
            'Unknown' => $allUnknown,
            'Initializing' => $allInitializing,
            'Compromised' => $allCompromised,
            'Fault' => $allFault,
            'NotPresent' => $allNotPresent,
            'devices' => $devicesRooms ?? 0,
            'NewNotif' => $NewNotif,
            'ResolvedNotif' => $ResolvedNotif,
            'UnresolvedNotif' => $UnresolvedNotif,
        ]);
        
    }
    public function getRegion(Request $request)
    {
        $country = $request->country;
        $user = Auth::user();
        $CompanyId = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as c', 'b.Company_Id', '=', 'c.Company_Id')
                        ->select('c.*')
                        ->where('a.id', $user->id)
                        ->where('c.Country',$country)
                        ->get();
        return response()->json([
            'CompanyId' => $CompanyId
        ]);
    }
    public function getRooms(Request $request){ //Available Rooms display

        $user = Auth::user();
        $orgId = $request->gOrgId;
        $Country = $request->gCountry;
        //Get all the Rooms under that specific region and organization
        $Rooms = DB::table('users as a')
                ->join('user_accesses as b','a.id','=','b.User_Id')
                ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                ->select('d.DeviceRoomName','d.DeviceRoomID')
                ->where('a.id',$user->id)
                ->where('c.Company_Id',$orgId)
                ->distinct()
                ->get();

        return response()->json([
            'Rooms' => $Rooms
        ]);
    }
    public function InitUptime(Request $request){
        //Setting up the date
        $date_range =  $request->initialDateRange;
        [$start_date, $end_date] = explode(' - ', $date_range);
        $start_date = Carbon::createFromFormat('m/d/Y', $start_date)->startOfDay();
    
        //  $formatted_start_date = $start_date->format('Y-m-d');
        $end_date = Carbon::createFromFormat('m/d/Y', $end_date)->endOfDay();
        //$formatted_end_date = $end_date->format('Y-m-d');

        // Calculate difference in days
        $diffInDays =floor($start_date->diffInDays($end_date));
        $DiffUptime = $diffInDays *100;
        $user = Auth::user();
        $UserAccess = DB::table('user_accesses')
                        ->select('Company_Id')
                        ->distinct()
                        ->where('User_Id',$user->id)
                        ->get();
        $array =[];
    
        $companyIds = collect($UserAccess)->pluck('Company_Id')->toArray(); //make it to array
            $Devices = DB::table('Devices as a')
                        ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                        ->select('a.Device_Id','a.Device_Name','a.Manufacturer','a.Room_Type','a.Device_Loc','a.IP_Address','a.Serial_Number')
                        ->whereIn('b.Company_Id',$companyIds)
                        ->where('a.Status','!=','Removed')
                        ->get();
                        foreach ($Devices as $Device) {
                            // Get the date only
                            $StartTime =   DB::table('device_histories')
                            ->select(DB::raw('DATE(Previous_Date) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('StartTime');
                        
                            $EndTime = DB::table('device_histories')
                            ->select(DB::raw('DATE(created_at) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('EndTime');

                            ////////convert time into seconds
                            $STime =  DB::table('device_histories')
                            ->select(DB::raw('TIME(Previous_Date) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('StartTime');
                            list($hrs, $mins, $secs) = sscanf($STime, "%d:%d:%d");
                            $totalSecondsST = ($hrs * 3600) + ($mins * 60) + $secs;

                            $ETime =  DB::table('device_histories')
                            ->select(DB::raw('TIME(created_at) AS EndTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('EndTime');
                            list($hours, $minutes, $seconds) = sscanf($ETime, "%d:%d:%d");
                            $totalSecondsDT = ($hours * 3600) + ($minutes * 60) + $seconds;
                            ////////end of convert time into seconds

                            if($StartTime != $EndTime){
                            //compute for the created_at only
                            $wholeday = 86400;//seconds 2024-06-26 17:30:58
                            $downtime = $totalSecondsDT;
                            $Uptime = ($wholeday - $downtime)/$wholeday *100;
                            if($Uptime<0){
                                $Uptime=0;
                            }else if ($Uptime>100){
                                $Uptime=100;
                            }
                            $uptime=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) . "%";
                            $percent=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) ;

                            }else{
                            $wholeday = 86400;//seconds
                            $downtime = $totalSecondsDT - $totalSecondsST;
                            $Uptime   = ($wholeday - $downtime) / $wholeday * 100;
                            if($Uptime<0){
                                $Uptime=0;
                            }else if ($Uptime>100){
                                $Uptime=100;
                            }
                            $uptime=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) . "%";
                            $percent=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) ;

                            }
                            $history = DeviceHistory::where('Device_Id', $Device->Device_Id)
                                ->where('Status', 'Offline')
                                ->whereBetween('created_at', [$start_date, $end_date])
                                ->orderBy('created_at')
                                ->get();

                            $uptimeData[] = [
                                "Device_Name" => $Device->Device_Name,
                                "Manufacturer" => $Device->Manufacturer,
                                "Room_Type" => $Device->Room_Type,
                                "Location" => $Device->Device_Loc,
                                "IP_Address" => $Device->IP_Address,
                                "Serial_Number" => $Device->Serial_Number,
                                "Uptime" => $uptime,
                                "percent" => $percent,
                            // "Total_Duration" => '', // Optional: You might want to include this information
                            //  "Offline_Duration" => '', // Optional: You might want to include this information
                                // "Online_Duration" => '', // Optional: You might want to include this information
                                "Incidents" => $history->count(),
                            ];
                        
                            $totalPercentage = 0; // Initialize total percentage
                            $count = 0;
                            $array[]=$uptimeData;
                            foreach ($uptimeData as $device) {
                                $count++;
                                $uptime = floatval($device['Uptime']);
                            // $uptime = rtrim($uptime, '%');
                            //  $uptimeInt = (int)$uptime;
                                $totalPercentage += $uptime;
                            }
                            if($count != 0){
                                $ave = $totalPercentage / $count;
                            }
        }
        return response()->json([
            'data' => $uptimeData,
            'ave' =>number_format($ave, 3),
        ]);
    }
    public function getOfflineIncident(Request $request){//Device Offline Incidents
        $device_id = $request->device;
        $histories = DeviceHistory::where('Device_ID', $device_id)->get();
        $offline_dates = [];
        
        if ($histories->count() > 0) {
            foreach ($histories as $history) {
                if ($history->Status == "Offline") {
                    $start_date = strtotime($history->Previous_Date);
                    $end_date = strtotime($history->created_at);
                    $current_date = $start_date;

                    while ($current_date <= $end_date) {
                        $offline_dates[] = date("F j, Y", $current_date);
                        $current_date = strtotime('+1 day', $current_date);
                    }

                    // Add the created_at date if it's different from the previous date
                    if ($end_date != $start_date) {
                        $offline_dates[] = date("F j, Y", $end_date);
                    }
                }
                if ($history->Status == "Missing") {
                    $start_date = strtotime($history->Previous_Date);
                    $end_date = strtotime($history->created_at);
                    $current_date = $start_date;

                    while ($current_date <= $end_date) {
                        $offline_dates[] = date("F j, Y", $current_date);
                        $current_date = strtotime('+1 day', $current_date);
                    }
                    // Add the created_at date if it's different from the previous date
                    if ($end_date != $start_date) {
                        $offline_dates[] = date("F j, Y", $end_date);
                    }
                }
                if ($history->Status == "Online") {
                    if ($histories->count() > 0) {
                        $formatted_date = date("F j, Y", strtotime($history->created_at));
                        $date = $formatted_date;
                        $offline_dates[] = $date;
                    }
                }
                if ($history->Status == "OK") {
                    if ($histories->count() > 0) {
                        $formatted_date = date("F j, Y", strtotime($history->created_at));
                        $date = $formatted_date;
                        $offline_dates[] = $date;
                    }
                }
            }
        }

        return response()->json([
            'offline_dates' => $offline_dates
        ]);
    }
    public function DeleteCompanyProfile(Request $request){
        $Company_Id = $request->gCompany_Id;
        try {
            CompanyProfile::where('Company_Id', $Company_Id)->delete();
            LogJob::dispatch($this->authUser,'Deleted a company profile '.$Company_Id.' ');

            return response()->json([
                'success' => 'successs'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'Error' =>'function DeleteCompanyProfile - '. $e->getMessage()
            ]);
        }
    }

    public function DeleteApiAccount(Request $request){
        $Api_Id = $request->gApi_Id;

        try {
            ApiAccount::where('Api_Id', $Api_Id)->delete();
            LogJob::dispatch($this->authUser,'Deleted API Account '.$Api_Id.' ');
            return response()->json([
                'success' => 'successs'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'Error' =>'function DeleteApiAccount - '.  $e->getMessage()
            ]);
        }
    }
    public function reliableRooms(Request $request){
    //Initialization 
    try{
        $totalPercentage = 0;
        $count = 0;
        $ReliableRooms = [];
        $aggregatedData = [];
        $CurrentUser = Auth::user();
        $DeviceRoom = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','c.Company_Id','=','b.Company_Id')
                        ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                        ->select('d.DeviceRoomName','d.DeviceRoomLocation')
                        ->where('a.id',$CurrentUser->id)
                        ->distinct()
                        ->get();
        //Computation of the total uptime of rooms
        $date_range =  $request->date_range;
        [$start_date, $end_date] = explode(' - ', $date_range);
        $start_date = Carbon::createFromFormat('m/d/Y', $start_date)->startOfDay();
        $formatted_start_date = $start_date->format('Y-m-d');
        $end_date = Carbon::createFromFormat('m/d/Y', $end_date)->endOfDay();
        $formatted_end_date = $end_date->format('Y-m-d');

        $Devices =DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','c.Company_Id','=','b.Company_Id')
                    ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                    ->join('devices as e','e.Api_Id','=','c.Api_Id')
                    ->select('e.Device_Id','e.Device_Name','e.Manufacturer','e.Room_Type','e.Device_Loc','e.Serial_Number','e.Status')
                    ->where('a.id',$CurrentUser->id)
                    ->where('c.Company_Id',$request->orgId)
                    ->where('e.Status','!=','Removed')
                    ->distinct()
                    ->get();
        foreach($Devices as $Device){
            $StartTime =   DB::table('device_histories')
                            ->select(DB::raw('DATE(Previous_Date) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('StartTime');
            $EndTime = DB::table('device_histories')
                            ->select(DB::raw('DATE(created_at) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('EndTime');
            $STime =  DB::table('device_histories')
                            ->select(DB::raw('TIME(Previous_Date) AS StartTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('StartTime');
                            list($hrs, $mins, $secs) = sscanf($STime, "%d:%d:%d");
                            $totalSecondsST = ($hrs * 3600) + ($mins * 60) + $secs;
            $ETime =  DB::table('device_histories')
                            ->select(DB::raw('TIME(created_at) AS EndTime'))
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('Device_ID',$Device->Device_Id)
                            ->where('Status','Offline')
                            ->value('EndTime');
                            list($hours, $minutes, $seconds) = sscanf($ETime, "%d:%d:%d");
                            $totalSecondsDT = ($hours * 3600) + ($minutes * 60) + $seconds;
            if($StartTime != $EndTime){
                $wholeday = 86400; //compute for the created_at only
                $downtime = $totalSecondsDT;
                $Uptime = ($wholeday - $downtime)/$wholeday *100;
                if($Uptime<0){
                    $Uptime=0;
                } 
                $uptime=round($Uptime, 2) . "%";
                $percent=round($Uptime, 2);
            }else{
                $wholeday = 86400;//seconds
                $downtime = $totalSecondsDT - $totalSecondsST;
                $Uptime   = ($wholeday - $downtime) / $wholeday * 100;
                if($Uptime<0){
                    $Uptime=0;
                }
                $uptime=round($Uptime, 2) . "%";
                $percent=round($Uptime, 2);
            }
                $uptimeData[] = [
                    "Room_Type" => $Device->Room_Type,
                    "Location" => $Device->Device_Loc,
                    "Uptime" => $uptime,
                    "percent" => $percent,
                ];
        }
        foreach ($uptimeData as $uptimeData) {
            $key = $uptimeData['Room_Type'] . '-' . $uptimeData['Location'];
            
            if (!isset($aggregatedData[$key])) {
                $aggregatedData[$key] = [
                    'Room_Type' => $uptimeData['Room_Type'],
                    'Location' => $uptimeData['Location'],
                    'Total_Uptime' => 0,
                    'Count' => 0,
                    'AverageUptime' => 0
                ];
            }
            
            $aggregatedData[$key]['Total_Uptime'] += $uptimeData['percent'];
            $aggregatedData[$key]['Count'] += 1;
            $aggregatedData[$key]['AverageUptime'] =  $aggregatedData[$key]['Total_Uptime']/ $aggregatedData[$key]['Count'];
        }

        $results = array_values($aggregatedData);
        $asc = $results;
        $desc = $results;
        usort($asc, function($a, $b) {
            return $a['AverageUptime'] <=> $b['AverageUptime'];
        });
        usort($desc, function($a, $b) {
            return $b['AverageUptime'] <=> $a['AverageUptime'];
        });
                return response()->json([
            'desc' => $desc,
            'asc' => $asc,
        ]);
    }catch (\Throwable $e) {
          return response()->json([
              'error' => $e->getMessage()
          ]);
      }
    }
    public function editCompanyProfile(Request $request) {
        try{
            $id = $request->CompanyID;

            $data = CompanyProfile::where('Company_id', $id)->first();
            return response()->json([
                'data' => $data
            ]);
            } catch (\Throwable $e) {
            return response()->json([
                'Error' => 'function editCompanyProfile - '.$e->getMessage()
            ]);
        }
    }
    public function updateCompanyProfile(Request $request)
    {
        try{
        $id = $request->code_id;
        $data = SystemConfiguration::where('Code_ID', $id)->first();
        if ($data) {
            if($data->Code_Name != $request->update_name){
                LogJob::dispatch($this->authUser,'Updated system configuration'.$id.' from '.$data->Code_Name.' to '.$request->update_name.' ');
                $data->Code_Name = $request->update_name;
            }
            if($data->Code_Description != $request->update_desc){
                LogJob::dispatch($this->authUser,'Updated system configuration'.$id.' from '.$data->Code_Description.' to '.$request->update_desc.' ');
                $data->Code_Description = $request->update_desc;
            }
            if($data->Code_Value != $request->update_value){
                LogJob::dispatch($this->authUser,'Updated system configuration'.$id.' from '.$data->Code_Value.' to '.$request->update_value.' ');
                $data->Code_Value = $request->update_value;
            }
            $data->save();
        }

        return response()->json([
            'success' => "System Configuration Updated!"
        ]);
        } catch (\Throwable $e) {
            return response()->json([
                'Error' => 'function updateCompanyProfile - '.$e->getMessage()
            ]);
        }
    }
    //Retrieve all per tables
    private function authUser(){
        return Auth::user();
    }
    private function apiAccounts(){
        $Auth = $this->authUser();
        return  DB::table('users as a')
                ->join('user_accesses as b','a.id','=','b.User_Id')
                ->join('company_profile_details as c','c.Company_Id','=','b.Company_Id')
                ->join('api_accounts as d','d.Api_Id','=','c.Api_Id')
                ->select('d.Api_Id','d.Platform','d.Description','d.Variable1','d.Varialble2','d.Variable3','d.Variable4','d.Variable5')
                ->where('a.id',$Auth->id)
                ->distinct()
                ->get();
    }
    private function companyProfiles(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profiles as c','b.Company_Id','=','c.Company_Id')
                    ->select('c.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function companyProfileDetails(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->select('c.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function devices(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('devices as d','d.Api_Id','=','c.Api_Id')
                    ->select('d.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function deviceDetails(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('devices as d','d.Api_Id','=','c.Api_Id')
                    ->join('device_details as e','d.Device_Id','=','e.Device_Id')
                    ->select('e.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function deviceHistories(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('devices as d','d.Api_Id','=','c.Api_Id')
                    ->join('device_histories as e','d.Device_Id','=','e.Device_ID')
                    ->select('e.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function deviceRooms(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                    ->select('d.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function systemConfiguration(){
        return DB::table('system_configurations')
                    ->select('*')
                    ->distinct()
                    ->get();
    }
    private function users(){
        return DB::table('users')
                    ->select('*')
                    ->distinct()
                    ->get();
    }
    private function userAccess(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->select('b.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function zohoCredentials(){
        return DB::table('zoho_credentials')
                    ->select('*')
                    ->distinct()
                    ->get();
    }
    private function zohoDesks(){
        $Auth = $this->authUser();
        return DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('devices as d','d.Api_Id','=','c.Api_Id')
                    ->join('zoho_desks as e','d.Device_Id','=','e.Device_Id')
                    ->select('e.*')
                    ->where('a.id',$Auth->id)
                    ->distinct()
                    ->get();
    }
    private function uptimeDevice(){
          //Uptime by device
        $array =[];
        $start_date =Carbon::now()->startOfDay();
        $end_date =  Carbon::now()->endOfDay();
        $diffInDays =floor($start_date->diffInDays($end_date));
        $DiffUptime = $diffInDays *100;
        $user = Auth::user();
        $UserAccess = $this->userAccess();
        $companyIds = collect($UserAccess)->pluck('Company_Id')->toArray(); //make it to array
         
        $Devicess = DB::table('Devices as a')
                        ->join('company_profile_details as b','a.Api_Id', '=', 'b.Api_Id')
                        ->select('a.Device_Id','a.Device_Name','a.Manufacturer','a.Room_Type','a.Device_Loc','a.IP_Address','a.Serial_Number','a.Status')
                        ->whereIn('b.Company_Id',$companyIds)
                        ->where('a.Status','!=','Removed')
                        ->get();
                        
        foreach ($Devicess as $Device) {
           
            // Get the date only
            $StartTime =   DB::table('device_histories')
            ->select(DB::raw('DATE(Previous_Date) AS StartTime'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('Device_ID',$Device->Device_Id)
            ->where('Status','Offline')
            ->value('StartTime');
        
            $EndTime = DB::table('device_histories')
            ->select(DB::raw('DATE(created_at) AS StartTime'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('Device_ID',$Device->Device_Id)
            ->where('Status','Offline')
            ->value('EndTime');

            ////////convert time into seconds
            $STime =  DB::table('device_histories')
            ->select(DB::raw('TIME(Previous_Date) AS StartTime'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('Device_ID',$Device->Device_Id)
            ->where('Status','Offline')
            ->value('StartTime');
            list($hrs, $mins, $secs) = sscanf($STime, "%d:%d:%d");
            $totalSecondsST = ($hrs * 3600) + ($mins * 60) + $secs;

            $ETime =  DB::table('device_histories')
            ->select(DB::raw('TIME(created_at) AS EndTime'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('Device_ID',$Device->Device_Id)
            ->where('Status','Offline')
            ->value('EndTime');
            list($hours, $minutes, $seconds) = sscanf($ETime, "%d:%d:%d");
            $totalSecondsDT = ($hours * 3600) + ($minutes * 60) + $seconds;
            ////////end of convert time into seconds
           
            if($StartTime != $EndTime){
            //compute for the created_at only
            $wholeday = 86400;//seconds 2024-06-26 17:30:58
            $downtime = $totalSecondsDT;
            $Uptime = ($wholeday - $downtime)/$wholeday *100;
            if($Uptime<0){
                $Uptime=0;
            }else if ($Uptime>100){
                $Uptime=100;
            }
            $uptime=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) . "%";
            $percent=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) ;

            }else{
            $wholeday = 86400;//seconds
            $downtime = $totalSecondsDT - $totalSecondsST;
            $Uptime   = ($wholeday - $downtime) / $wholeday * 100;
            if($Uptime<0){
                $Uptime=0;
            }else if ($Uptime>100){
                $Uptime=100;
            }
            $uptime=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) . "%";
            $percent=round(($Uptime+$DiffUptime)/($diffInDays+1), 2) ;

            }
         
            $history = DeviceHistory::where('Device_Id', $Device->Device_Id)
                ->where('Status', 'Offline')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('created_at')
                ->get();

            $uptimeData[] = [
                "Device_Name" => $Device->Device_Name,
                "Manufacturer" => $Device->Manufacturer,
                "Room_Type" => $Device->Room_Type,
                "Location" => $Device->Device_Loc,
                "IP_Address" => $Device->IP_Address,
                "Serial_Number" => $Device->Serial_Number,
                "Uptime" => $uptime,
                "percent" => $percent,
            // "Total_Duration" => '', // Optional: You might want to include this information
                //  "Offline_Duration" => '', // Optional: You might want to include this information
                // "Online_Duration" => '', // Optional: You might want to include this information
                "Incidents" => $history->count(),
            ];
        }
        $Date = DB::table('users')
        ->select('updated_at')
        ->where('email',$user->email)
        ->get();
        $UDate = \DateTime::createFromFormat('Y-m-d H:i:s', $Date[0]->updated_at);
        $currentDateTime = new \DateTime();
        $difference =  $currentDateTime->diff($UDate); //uncomment day computetaion 
        $daysDifference = $difference->days;
    
        return $uptimeData;
    }
    private function ave($uptimeData){
        $totalPercentage = 0; // Initialize total percentage
        $count = 0;
        $array[]=$uptimeData;
        foreach ($uptimeData as $device) {
            $count++;
            $uptime = floatval($device['Uptime']);
            $totalPercentage += $uptime;
        }
        if($count != 0){
            $ave = $totalPercentage / $count;
        }
        return $ave;
    }
    public function sessionTimeout(Request $request){
        $auth = $this->authUser();
        
        if($auth->usertype!=0){
       
        }else{
            LogJob::dispatch($this->authUser,'Session expired');
            $today = Carbon::now()->toDateString();
            User::whereDate('updated_at', '<>', $today)
                ->whereNotIn('usertype',[1,2])
                ->update(['remember_token' => null]);
        }

        return response()->json([
            'auth' => $auth,
        ]);

    }
    public function sessionTimeoutSave(Request $request){
        try {
            $auth = Auth::user(); // Assumes the user is authenticated
    
            // Validate the request to ensure it contains the necessary fields
            $request->validate([
                'sessionPassword' => 'required|string',
            ]);
    
            // Retrieve the plain-text password from the request
            $plainPassword = $request->sessionPassword;
    
            // The hashed password stored in the authenticated user model
            $storedHashedPassword = $auth->password;
    
            // Check if the plain-text password matches the stored hashed password
            if (!Hash::check($plainPassword, $storedHashedPassword)) {
                return response()->json(['message' => 'Not validated']); // Return JSON response with error status
            } else {
                return response()->json(['message' => 'Validated']); // Return JSON response with success status
            }
        } catch (\Throwable $e) {
            // Return error response in case of exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function dashboardDisplay(){
        $usertype = Auth::user();//fix this
        $ApiAccounts = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.User_Id')
                        ->join('company_profile_details as c','c.Company_Id','=','b.Company_Id')
                        ->join('api_accounts as d','d.Api_Id','=','c.Api_Id')
                        ->select('d.*')
                        ->where('a.id',$usertype->id)
                        ->distinct()
                        ->get();
        $CompanyProfiles = DB::table('company_profiles as a')
                            ->join('user_accesses as b','a.Company_Id','=','b.Company_Id')
                            ->join('users as c','c.id','=','b.User_Id')
                            ->select('a.*')
                            ->where('c.id',$usertype->id)
                            ->get();
        $CID = DB::table('company_profiles as a')
                    ->join('user_accesses as b','a.Company_Id','=','b.Company_Id')
                    ->join('users as c','c.id','=','b.User_Id')
                    ->select('a.*')
                    ->where('c.id',$usertype->id)
                    ->get();//where statement pa using the auth id
        $companyIds =$CID->pluck('Company_Id')->toArray(); 
   
        $Devices =  DB::table('company_profiles as a')
                        ->join('company_profile_details as b','a.Company_Id','=','b.Company_Id')
                        ->join('api_accounts as c','b.Api_Id','=','c.Api_Id')
                        ->join('devices as d','c.Api_Id','=','d.Api_Id')
                        ->select('d.*')
                        ->whereIn('a.Company_Id', $companyIds)
                        ->where('d.Status', '!=', 'removed')
                        ->orderBy('a.Company_Name', 'asc')
                        ->get();//display of devices
     
        $Rooms = DB::table('users as a')
                ->join('user_accesses as b','a.id','=','b.User_Id')
                ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                ->select('d.*')
                ->where('a.id',$usertype->id)
                ->whereIn('c.Company_Id', $companyIds)
                ->distinct()
                ->get();
        $NewNotif = DB::table('users as a')
                    ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                    ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                    ->select(DB::raw('count(*) as new_count')) // Alias the count result for easier access
                    ->whereIn('c.Status', ['Open','new'])
                    ->whereDate('c.created_at', Carbon::today()) 
                    ->where('a.id', $usertype->id)
                    ->first();
        $ResolvedNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                        ->select(DB::raw('count(*) as resolved_count')) // Alias the count result for easier access
                        ->where('c.Status', 'Closed')
                        ->where('a.id', $usertype->id)
                        ->first();
        $UnresolvedNotif = DB::table('users as a')
                            ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                            ->join('zoho_desks as c', 'b.Company_Id', '=', 'c.Company_Id')
                            ->select(DB::raw('count(*) as unresolved_count')) // Alias the count result for easier access
                            ->where('c.Status', 'Open')
                            ->where('a.id', $usertype->id)
                            ->first();
            return [
                'ApiAccounts' => $ApiAccounts,
                'CompanyProfiles' => $CompanyProfiles,
                'CID' => $CID,
                'Devices' => $Devices,
                'Rooms' => $Rooms,
                'NewNotif' => $NewNotif,
                'ResolvedNotif' => $ResolvedNotif,
                'UnresolvedNotif' => $UnresolvedNotif];
    }
}
