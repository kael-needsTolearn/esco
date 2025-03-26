<?php

namespace App\Http\Controllers;

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
use App\Services\MySharedService;
use Illuminate\Http\Client\RequestException;
use App\Jobs\uhooUpdate;
use App\Jobs\CreationOfTicketsJob;
use App\Jobs\uhooCreateTicket;

class HomeController extends Controller
{
    public $timestamps = false;
    protected $user;
    protected $ForgetPassData;
    protected $email;
    protected $authUser;
    protected $MySharedService;

    public function __construct(MySharedService $MySharedService)//SystemConfig $SystemConfig
    {
        $this->authUser = Auth::user();
        $this->MySharedService = $MySharedService;
    }
 
    public function index(){

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
                dd('error');
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
        }
     }catch(\Throwable $e){
        dd($e->getMessage());
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
            $uhooAccess = $this->uhoo_access();

            
        } catch (\Throwable $e) {
            dd($e->getMessage());
            // Auth::guard('web')->logout();
            // abort(401, 'No User Access ask for the admin');
            // Session::flush();
            // return;
        }
        return view('admin.dashboard.home',
                ['CompanyProfiles'=>$Display['CompanyProfiles'],
                'DeviceRoom'=>$Display['Rooms'],
                'NewNotif'=>$Display['NewNotif'],
                'ResolvedNotif'=>$Display['ResolvedNotif'],
                'Devices'=>$Display['Devices'],
                'data' => $uptimeData,
                'ave' =>number_format($ave, 3),
                'auth' =>$auth,
                'UnresolvedNotif'=>$Display['UnresolvedNotif'],
                'uhoo'=>$uhooAccess
                ]);
    }
    public function refreshDashboard(){
        try{
            $auth = $this->authUser();
            $Display = $this->dashboardDisplay();
            $uptimeData = $this->uptimeDevice();
            $ave = $this->ave($uptimeData);
            $uhooAccess = $this->uhoo_access();

        } catch (\Throwable $e) {
            dd($e->getMessage());
            // Auth::guard('web')->logout();
            // abort(401, 'No User Access ask for the admin');
            // Session::flush();
            // return;
        }
        return response()->json([
                'CompanyProfiles'=>$Display['CompanyProfiles'],
                'DeviceRoom'=>$Display['Rooms'],
                'NewNotif'=>$Display['NewNotif'],
                'ResolvedNotif'=>$Display['ResolvedNotif'],
                'Devices'=>$Display['Devices'],
                'data' => $uptimeData,
                'ave' =>number_format($ave, 3),
                'auth' =>$auth,
                'uhoo'=>$uhooAccess,
                'UnresolvedNotif'=>$Display['UnresolvedNotif']
                ]);
    }
    public function refreshDevice(){
        try{
       
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
            $samp =[];             
            //\Log::info('next.');
            foreach ($apis as $api) {
                \Log::info('next.'.$api->Variable2);
                if ($api->Platform == 'xio') {
                  
                    $subscriptionKey = Crypt::decryptString($api->Variable1);
                    \Log::info('next.'.$subscriptionKey);
                    $accountId =Crypt::decryptString($api->Variable2);
                    \Log::info('INFO::', ['SubscriptionKey' => $subscriptionKey, 'accountId' => $accountId]);
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
                }else{
                    \Log::info('Different API.');
                }  
            }
            $this->MySharedService->Service_RefreshDevice(auth()->id());
            $this->MySharedService->Service_Version2(auth()->id());
            $this->MySharedService->Service_CreateTicket(auth()->id());
            $this->MySharedService->Service_CommentInTicket(auth()->id());
            $this->MySharedService->Service_CommentDeviceRemoved(auth()->id());
            $this->MySharedService->Service_UpdateTicket(auth()->id());
            $this->MySharedService->Service_UpdateStatus(auth()->id());

        \Log::info('refreshDevice job dispatched.');
        
        } catch(\Throwable $e){
            \Log::error('refreshDevice job dispatched failed. '.$e->getMessage());
        }
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

                $uhooAccess = $this->uhoo_access();

                return response()->json([
                    'Devices' => $Devices,
                    'DeviceStatus' => $Company,
                    'NotificationSumm' => $NotificationSummary,
                    'DeviceOfflineIncidets' => $CountDevices,
                    'uhoo'=>$uhooAccess,
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
        $auth = Auth::user();
        $uhoo = $this->uhoo_access();

        return view('admin.dashboard.reports', compact('devices','region','auth','uhoo'));
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
                        ->select('f.*','d.Device_Name',DB::raw("DATE_FORMAT(f.updated_at, '%M %d %Y') as updated_at"))
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
                        ->select('f.Ticket_Id','f.Ticket_Number','f.Company_Id','f.Device_Id','f.Subject','f.Status','f.Remarks','f.Log_Last_Online','f.Elapse_Time',DB::raw("DATE_FORMAT(f.created_at, '%M %d %Y') as created_at"),'d.Device_Name',DB::raw("DATE_FORMAT(f.updated_at, '%M %d %Y') as updated_at"))
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
                        ->select('f.Ticket_Id','f.Ticket_Number','f.Company_Id','f.Device_Id','f.Subject','f.Status','f.Remarks','f.Log_Last_Online','f.Elapse_Time','f.created_at','d.Device_Name',DB::raw("DATE_FORMAT(f.updated_at, '%M %d %Y') as updated_at"))
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
                'AuthUser' => $Auth,
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
            }catch(\Throwable $e){
                return response()->json([
                    'Error'=>'function AlertNotification - '.$e->getMessage()
                ]);
            }
        
    }
    public function ChangeTicketStatus(Request $request){
        $user = Auth::user();
        if($user->usertype==2){
            if($request->org ==''){
                $DevicesOfUser =  DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('c.Status',$request->status)
                ->where('d.Country',$request->gRegion)
                ->distinct()
                ->get();
            }else{
                $DevicesOfUser =  DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('c.Status',$request->status)
                ->where('d.Country',$request->gRegion)
                ->where('d.Company_Id',$request->org)
                ->distinct()
                ->get();
            }
            
          //  dd($DevicesOfUser);
        return response()->json([
            'DevicesOfUser' => $DevicesOfUser,
        ]);
        }else{
        if($request->org ==''){
            $DevicesOfUser =  DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                        ->where('c.Status',$request->status)
                        ->where('d.Country',$request->gRegion)
                        ->where('b.User_Id',$user->id)
                        ->distinct()
                        ->get();
                        //dd($DevicesOfUser);
            }else{
                $DevicesOfUser =  DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('c.Status',$request->status)
                ->where('d.Country',$request->gRegion)
                ->where('b.User_Id',$user->id)
                ->where('d.Company_Id',$request->org)
                ->distinct()
                ->get();
            }
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
                if($request->org==''){
                    $TicketsByDate = DB::table('users as a')
                    ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                    ->join('company_profiles as d','d.company_Id','=','b.company_id')
                    ->join('company_profile_details as e','e.company_id','=','d.company_id')
                    ->join('api_accounts as f','f.api_id','=','e.api_id')
                    ->join('devices as g','g.api_id','=','f.api_id')
                    ->join('zoho_desks as c','c.device_id','=','g.device_id')
                    ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                    ->where('d.Country',$request->gRegion)
                    ->whereDate('c.created_at', '>=', $startDate)
                    ->whereDate('c.created_at', '<=', $endDate)
                    ->distinct()
                    ->get();
                }else{
                    $TicketsByDate = DB::table('users as a')
                    ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                    ->join('company_profiles as d','d.company_Id','=','b.company_id')
                    ->join('company_profile_details as e','e.company_id','=','d.company_id')
                    ->join('api_accounts as f','f.api_id','=','e.api_id')
                    ->join('devices as g','g.api_id','=','f.api_id')
                    ->join('zoho_desks as c','c.device_id','=','g.device_id')
                    ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                    ->where('d.Country',$request->gRegion)
                    ->where('d.Company_Id',$request->org)
                    ->whereDate('c.created_at', '>=', $startDate)
                    ->whereDate('c.created_at', '<=', $endDate)
                    ->distinct()
                    ->get();
                }
                    return response()->json([
                        'TicketsByDate' => $TicketsByDate,
                    ]);
                }else{
                    if($request->org==''){
                        $TicketsByDate = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                        ->where('d.Country',$request->gRegion)
                        ->where('c.Status',$request->gStatus)
                        ->whereDate('c.created_at', '>=', $startDate)
                        ->whereDate('c.created_at', '<=', $endDate)
                        ->distinct()
                        ->get();
                    }else{
                        $TicketsByDate = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                        ->where('d.Country',$request->gRegion)
                        ->where('c.Status',$request->gStatus)
                        ->whereDate('c.created_at', '>=', $startDate)
                        ->whereDate('c.created_at', '<=', $endDate)
                        ->where('d.Company_Id',$request->org)
                        ->distinct()
                        ->get();
                    }
                    return response()->json([
                        'TicketsByDate' => $TicketsByDate,
                    ]);
                }
        }else{
        if($request->gStatus === null){
            if($request->org==''){
                $TicketsByDate = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('b.User_Id',$user->id)
                ->where('d.Country',$request->gRegion)
                //->where('b.Status',$request->status)
                ->whereDate('c.created_at', '>=', $startDate)
                ->whereDate('c.created_at', '<=', $endDate)
                ->distinct()
                ->get();
            }else{
                $TicketsByDate = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('b.User_Id',$user->id)
                ->where('d.Country',$request->gRegion)
                ->where('d.Company_Id',$request->org)
                //->where('b.Status',$request->status)
                ->whereDate('c.created_at', '>=', $startDate)
                ->whereDate('c.created_at', '<=', $endDate)
                ->distinct()
                ->get();
            }
                return response()->json([
                    'TicketsByDate' => $TicketsByDate,
                ]);
        }else{
            if($request->org==''){
                $TicketsByDate = DB::table('users as a')
                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                ->join('api_accounts as f','f.api_id','=','e.api_id')
                ->join('devices as g','g.api_id','=','f.api_id')
                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                ->where('b.User_Id',$user->id)
                ->where('c.Status',$request->gStatus)
                ->where('d.Country',$request->gRegion)
                ->whereDate('c.created_at', '>=', $startDate)
                ->whereDate('c.created_at', '<=', $endDate)
                ->distinct()
                ->get();
            }else{
                $TicketsByDate = DB::table('users as a')
                                ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                                ->join('company_profiles as d','d.company_Id','=','b.company_id')
                                ->join('company_profile_details as e','e.company_id','=','d.company_id')
                                ->join('api_accounts as f','f.api_id','=','e.api_id')
                                ->join('devices as g','g.api_id','=','f.api_id')
                                ->join('zoho_desks as c','c.device_id','=','g.device_id')
                                ->select('c.*','g.Device_Name',DB::raw('DATE_FORMAT(c.created_at, "%M %d, %Y %l:%i%p") as created_at'),'g.Device_Name',DB::raw("DATE_FORMAT(c.updated_at, '%M %d, %Y %l:%i%p') AS formatted_date_time"))
                                ->where('b.User_Id',$user->id)
                                ->where('d.Company_Id',$request->org)
                                ->where('c.Status',$request->gStatus)
                                ->where('d.Country',$request->gRegion)
                                ->whereDate('c.created_at', '>=', $startDate)
                                ->whereDate('c.created_at', '<=', $endDate)
                                ->distinct()
                                ->get();
            }
            return response()->json([
                'TicketsByDate' => $TicketsByDate,
            ]);
        }
        }
    }
    public function UpdateField(Request $request){

        try{ 
            $oldData = DB::table('devices')
            ->select($request->gColumn)
            ->where('Device_Id', $request->gDevId)
            ->first();
            LogJob::dispatch($this->authUser,'Updated device '.$request->gDevId.' - '. $request->gColumn.' from '.$oldData->{$request->gColumn}.' to '.$request->Inputval.' ');

            DB::table('devices')
            ->where('Device_Id', $request->gDevId)
            ->update([$request->gColumn => $request->Inputval ,
                    'updated_at'=>now()]);

            return response()->json([
                'validated'=>'Successfully saved.'
            ]);    
        }catch(\Throwable $e){
            return response()->json([
                'response'=>'Wrong Password.'
            ]);
        }
       
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
            'AuthUser' => $Auth,


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
        //Get all the Rooms under that specific region and organization  //fix the org too
        $Rooms = DB::table('users as a')
                    ->join('user_accesses as b','a.id','=','b.User_Id')
                    ->join('company_profile_details as c','b.Company_Id','=','c.Company_Id')
                    ->join('device_rooms as d','d.Api_Id','=','c.Api_Id')
                    ->select('d.DeviceRoomName','d.DeviceRoomID')
                    ->where('a.id',$user->id)
                    ->where('c.Company_Id',$orgId)
                    ->distinct()
                    ->get();

        $NewNotif = DB::table('users as a')
                    ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                    ->join('company_profiles as d','d.company_Id','=','b.company_id')
                    ->join('company_profile_details as e','e.company_id','=','d.company_id')
                    ->join('api_accounts as f','f.api_id','=','e.api_id')
                    ->join('devices as g','g.api_id','=','f.api_id')
                    ->join('zoho_desks as c','c.device_id','=','g.device_id')
                    ->select(DB::raw('count(*) as new_count')) // Alias the count result for easier access
                    ->whereIn('c.Status', ['Open','new'])
                    ->whereDate('c.created_at', Carbon::today()) 
                    ->where('a.id', $user->id)
                    ->where('d.company_id',$request->gOrgId)
                    ->first();

        $ResolvedNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select(DB::raw('count(*) as resolved_count')) // Alias the count result for easier access
                        ->where('c.Status', 'Closed')
                        ->where('a.id', $user->id)
                        ->where('d.company_id',$request->gOrgId)
                        ->first();

        $UnresolvedNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select(DB::raw('count(*) as unresolved_count')) // Alias the count result for easier access
                        ->where('c.Status', 'Open')
                        ->where('a.id', $user->id)
                        ->where('d.company_id',$request->gOrgId)
                        ->first();
        //dd($Rooms,$NewNotif,$ResolvedNotif,$UnresolvedNotif);
        return response()->json([
            'Rooms' => $Rooms,
            'NewNotif' => $NewNotif,
            'ResolvedNotif' => $ResolvedNotif,
            'UnresolvedNotif' => $UnresolvedNotif
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
            $AssignedApi = DB::table('api_accounts as a')
                           ->join('company_profile_details as b','a.Api_Id','=','b.Api_Id')
                           ->select('a.Api_Id')
                           ->where('b.Api_Id',$Api_Id)
                           ->get();
                          // dd($AssignedApi,$Api_Id);
            if($AssignedApi->isEmpty()){
                ApiAccount::where('Api_Id', $Api_Id)->delete();
                LogJob::dispatch($this->authUser,'Deleted API Account '.$Api_Id.' ');
                return response()->json([
                    'success' => 'successs'
                ]);
            }else{
              //  dd('not here');
                return response()->json([
                    'Error' => 'There is assigned API to the company account(s)'
                ]);
            }
            
        } catch (\Throwable $e) {
          //dd($e->getMessage());
            return response()->json([
                'Error' =>'function DeleteApiAccount - '.  $e->getMessage()
            ]);
        }
    }
    public function reliableRooms(Request $request){
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
          try{
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
        } catch (\Throwable $e) {
            dd($e->getMessage());
            // Auth::guard('web')->logout();
            // abort(401, 'No User Access ask for the admin');
            // Session::flush();
            // return;
        }
    }
    private function ave($uptimeData){
        try{
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
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
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
        try{
        $usertype = Auth::user();//fix this
   
       // dd($ApiIdAssignedAlready);
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
                    ->join('company_profiles as d','d.company_Id','=','b.company_id')
                    ->join('company_profile_details as e','e.company_id','=','d.company_id')
                    ->join('api_accounts as f','f.api_id','=','e.api_id')
                    ->join('devices as g','g.api_id','=','f.api_id')
                    ->join('zoho_desks as c','c.device_id','=','g.device_id')
                    ->select(DB::raw('count(*) as new_count')) // Alias the count result for easier access
                    ->whereIn('c.Status', ['Open','new'])
                    ->whereDate('c.created_at', Carbon::today()) 
                    ->where('a.id', $usertype->id)
                    ->first();
        $ResolvedNotif = DB::table('users as a')
                        ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                        ->join('company_profiles as d','d.company_Id','=','b.company_id')
                        ->join('company_profile_details as e','e.company_id','=','d.company_id')
                        ->join('api_accounts as f','f.api_id','=','e.api_id')
                        ->join('devices as g','g.api_id','=','f.api_id')
                        ->join('zoho_desks as c','c.device_id','=','g.device_id')
                        ->select(DB::raw('count(*) as resolved_count')) // Alias the count result for easier access
                        ->where('c.Status', 'Closed')
                        ->where('a.id', $usertype->id)
                        ->first();
        $UnresolvedNotif = DB::table('users as a')
                            ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                            ->join('company_profiles as d','d.company_Id','=','b.company_id')
                            ->join('company_profile_details as e','e.company_id','=','d.company_id')
                            ->join('api_accounts as f','f.api_id','=','e.api_id')
                            ->join('devices as g','g.api_id','=','f.api_id')
                            ->join('zoho_desks as c','c.device_id','=','g.device_id')
                            ->select(DB::raw('count(*) as unresolved_count')) // Alias the count result for easier access
                            ->where('c.Status', 'Open')
                            ->where('a.id', $usertype->id)
                            ->first();

        $uhooAccess =   DB::table('users as a')
                            ->join('user_accesses as b','a.id','=','b.user_Id')
                            ->join('company_profiles as c','c.Company_Id','=','b.Company_Id')
                            ->join('company_profile_details as d','d.Company_Id','=','c.Company_Id')
                            ->join('api_accounts as e','e.Api_Id','=','d.Api_Id')
                            ->select('b.Company_Id')
                            ->where('a.id',$usertype->id)
                            ->where('e.Platform','uhoo')
                            ->get();
                           // dd($uhooAccess)
            return [
                'ApiAccounts' => $ApiAccounts,
                'CompanyProfiles' => $CompanyProfiles,
                'CID' => $CID,
                'Devices' => $Devices,
                'Rooms' => $Rooms,
                'NewNotif' => $NewNotif,
                'ResolvedNotif' => $ResolvedNotif,
                'uhoo' => $uhooAccess,
                'UnresolvedNotif' => $UnresolvedNotif];
            } catch (\Throwable $e) {
                dd($e->getMessage());
                // Auth::guard('web')->logout();
                // abort(401, 'No User Access ask for the admin');
                // Session::flush();
                // return;
            }
    
        }
    public function pwvalidation(request $request){
      
        $ValidateData = $request->validate([
            'validationpw'=>'required'
        ]);
        $user = $this->authUser();
        if($user){
            if (!Hash::check($ValidateData['validationpw'], $user->password)) {
                return response()->json([
                    'notvalidated' => 'Wrong Password.',
                ]);
            }
            return response()->json([
                'validated' => 'Successfully saved.',
            ]);
        }
    }
    public function uhooAccessCode(){
        //GET THE ACCESS TOKEN AND REFRESH TOKEN.
        $Auth = $this->authUser();

        $Token = DB::table('users as a')
                    ->join('user_accesses as b', 'a.id', '=', 'b.User_Id')
                    ->join('company_profiles as d','d.company_Id','=','b.company_id')
                    ->join('company_profile_details as e','e.company_id','=','d.company_id')
                    ->join('api_accounts as f','f.api_id','=','e.api_id')
                    ->select(DB::raw('f.Variable3')) // Alias the count result for easier access
                    ->where('f.Platform', 'uhoo')
                    ->where('a.id', $Auth->id)
                    ->first();
                    
        $generatetoken =Crypt::decryptString($Token->Variable3);
       // dd($generatetoken);

            $response = Http::withoutVerifying()->asForm()->post('https://api.uhooinc.com/v1/generatetoken', [
                'code' =>  $generatetoken
            ]);

        if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? null;

            if ($accessToken) {
              //  dd($accessToken,$data);
                return $accessToken;
            } else {
                return response()->json(['error' => 'Access token not found'], 400);
            }
        } else {
            return response()->json(['error' => 'API request failed', 'details' => $response->body()], $response->status());
        }
    } 
    public function uhooDisplay(Request $request){//wellness
 
        $Serial_Number = $request->query('kdsartkn231nkjh1k23hkjn12');
        $Device_Name = $request->query('DeviceName');

        $auth = Auth::user();
        $region = $this->region();
        $uhoo = $this->uhoo_access();
        $Serial = $request->Serial;
  
       
        return view('admin.dashboard.uhooDisplay',['auth' =>$auth,'region'=>$region,'Serial_Number'=>$Serial_Number,'Device_Name'=>$Device_Name,'uhoo'=>$uhoo]);
    }
    public function uhooDashboard(){ //wellness display data
        try{
            $auth = Auth::user();
            $region = $this->region();


            $conditions = ['Good' => 'green_content', 'Moderate' => 'yellow_content', 'Bad' => 'red_content'];
            $results = [];
                                
                                foreach ($conditions as $condition => $variable) {
                                    $results[$variable] = DB::table('uhoo_device_header as a')
                                        ->join('uhoo_device_details as b', 'a.Serial_Number', '=', 'b.Serial_Number')
                                        ->where('b.Condition', $condition)
                                        ->distinct('a.Serial_Number')
                                        ->count('a.Serial_Number');
                                }
                                
            $green_content = DB::table('uhoo_device_header as a')
                                ->join('uhoo_device_details as b', 'a.Serial_Number', '=', 'b.Serial_Number')
                                ->whereNotIn('b.Condition', ['Moderate', 'Bad'])
                                ->distinct('a.Serial_Number')
                                ->count('a.Serial_Number');

            $yellow_content = $results['yellow_content'];
            $red_content = $results['red_content'];
            $total = DB::table('uhoo_device_header')
                        ->count('Serial_Number');
            

            $Devices = DB::table('uhoo_device_header as a')
                        ->join('uhoo_device_details as b','a.Serial_Number','=','b.Serial_Number')
                        ->select('a.Serial_Number')
                        ->get();

            $arr=[];
            $ctr = 0; 
$huh = collect();
            foreach($Devices as $Serial){

                $huh = DB::table('uhoo_device_details as a')
                            ->join('uhoo_device_header as b','a.Serial_Number','=','b.Serial_Number')
                            ->select('a.Value','a.Label','a.Prev_Value','a.Condition','a.Measurement','a.Serial_Number','b.Device_Name')
                            ->where('a.Serial_Number',$Serial->Serial_Number)
                            ->where('a.Condition', '!=', '')
                            ->whereIn('a.Condition',["Bad","Moderate"])
                            ->distinct()
                            ->get();
            }
            if($huh->isNotEmpty()){
                $arr=$ctr;
                }else{
                    $ctr++;
                    $arr=$ctr;
                }
        $uhoo = $this->uhoo_access();
        
        
        return view('admin.dashboard.wellness',['auth' =>$auth,
                                                'region'=>$region,
                                                'green'=>$arr,
                                                'yellow'=>$yellow_content,
                                                'red'=>$red_content,
                                                'uhoo'=>$uhoo,
                                                'total'=>$total]);
        }catch(\Throwable $e){
            dd($e->getMessage());
            return response()->json([
                'Error' =>'function uhooDeviceData - '.  $e->getMessage()
            ]);
        }
    }
    public function uhooDeviceList(){//uHoo Device Header  insert data to database update
        try{
       
            $auth = Auth::user();
     
            \Log::info('INFO::', ['info1: ' => $auth]);
            $region = $this->region();
            
            \Log::info('INFO::', ['info2: ' => $region]);
            $accessToken = $this->uhooAccessCode();
          
            \Log::info('INFO::', ['info3: ' => $accessToken]);
            $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken
            ])->withoutVerifying()->get('https://api.uhooinc.com/v1/devicelist');
                
            $data = $response->json();
         // dd($data);
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

                    $url1 = 'https://api.uhooinc.com/v1/devicedata';
                    $data1 = [
                                'macAddress' => $data['macAddress'], //$macAddress
                                'mode' => 'hour' // Data mode 30
                            ];
                    $response1 = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $accessToken
                        ])->withoutVerifying()->asForm()->post($url1, $data1);
                        
                    $data1 = $response1->json(); //update for status only

                        if (isset($data1['message']) && $data1['message'] == "Device has no data.") {
                            DB::table('uhoo_Device_Header')
                            ->where('Mac_Address', $data['macAddress'])
                            ->update(['Status' => "Offline"]); 
                            
                        }else{
                            DB::table('uhoo_Device_Header')
                            ->where('Mac_Address', $data['macAddress'])
                            ->update(['Status' => "Online"]); 
                        }  

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
            
                    $url1 = 'https://api.uhooinc.com/v1/devicedata';
                    $data1 = [
                                'macAddress' => $data['macAddress'], //$macAddress
                                'mode' => 'hour' // Data mode 30
                            ];
                    $response1 = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $accessToken
                        ])->withoutVerifying()->asForm()->post($url1, $data1);
                        
                    $data1 = $response1->json();//update for status only

                        if (isset($data1['message']) && $data1['message'] == "Device has no data.") {
                            DB::table('uhoo_Device_Header')
                            ->where('Mac_Address', $data['macAddress'])
                            ->update(['Status' => "Offline"]); 
                            
                        }else{
                            DB::table('uhoo_Device_Header')
                            ->where('Mac_Address', $data['macAddress'])
                            ->update(['Status' => "Online"]); 
                        }  
                }
            }
            return response()->json([
                'MacAddress' => $MacAddress,
                'SerialNumber' => $SerialNumber
            ]);
        } catch(\Throwable $e){
            \Log::info('INFO::', ['infohuh: ' => $e->getMessage()]);
            return response()->json([
                'Error' =>'function uhooDeviceList - '.  $e->getMessage()
            ]);
        }
    }
    public function uhooDeviceData(){//This updates the uhoo main data
     
        try{//fix this
           
            $DataAccess = $this->uhooDeviceList();
            $accessToken = $this->uhooAccessCode();
        $ar =[];
        $mac =[];
$DataConvert = json_decode($DataAccess->getContent(), true);
   //dd($DataConvert);
            $combined = array_combine($DataConvert['MacAddress'], $DataConvert['SerialNumber']);
         
     
        foreach($combined as $Mac => $Serial){
            $ctr = 0;
            $url = 'https://api.uhooinc.com/v1/devicedata';
            $data = [
                        'macAddress' => $Mac, //$macAddress
                        'mode' => 'minute' // Data mode 30
                    ];
            $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken
                    ])->withoutVerifying()->asForm()->post($url, $data);
          
       
            $data = $response->json();
         
            $sensorData = $data['data'][$ctr];
            $Measurements = $data['usersettings'];
                foreach ($sensorData as $key => $value){
                    $exists = DB::table('uhoo_Device_Details')
                                ->where('Serial_Number', $Serial)
                                ->where('Label', $key)
                                ->exists();
                    $value = (float) $value;
                    if ($key == 'virusIndex') {
                        $condition = match (true) {
                            $value <= 3 => 'Good',
                            $value >= 4 && $value <= 8 => 'Moderate',
                            default => 'Bad',
                        };
                        }elseif($key == 'moldIndex') {
                            $condition = match (true) {
                                $value <= 3 => 'Good',
                                $value >= 4 && $value <= 8 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif ($key == 'temperature') {
                            $condition = match (true) {
                                $value < 15 => 'Bad',
                                $value <= 20 && $value >= 16 => 'Moderate',
                                $value >= 21 && $value <= 26 => 'Good',
                                default => 'Bad',
                            };
                        }elseif($key == 'humidity') {
                            $condition = match (true) {
                                $value >= 30 && $value <= 50 => 'Good', // Correct condition for Good
                                $value > 50 && $value <= 90 => 'Moderate', // Moderate range after Good
                                $value > 10 && $value < 30 => 'Moderate', // Lower range for Moderate
                                default => 'Bad',
                            };
                        }elseif($key == 'co2') {
                            $condition = match (true) {
                                $value >= 400 && $value <= 800 => 'Good',
                                $value >= 801 && $value <= 1500 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'tvoc') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 660 => 'Good',
                                $value >= 661 && $value <= 5500 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'ch2o') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 500 => 'Good',
                                $value >= 501 && $value <= 750 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'pm1') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 12 => 'Good',
                                $value >= 13 && $value <= 35 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'pm25') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 12 => 'Good',
                                $value >= 13 && $value <= 35 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'pm4') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 12 => 'Good',
                                $value >= 13 && $value <= 35 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'pm10') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 20 => 'Good',
                                $value >= 21 && $value <= 150 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'co') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 9 => 'Good',
                                $value > 9 && $value <= 35 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'airPressure') {
                            $condition = match (true) {
                                $value >= 600 && $value <= 970 => 'Moderate',
                                $value >= 971 && $value <= 1030 => 'Good',
                                $value >= 1031 && $value <= 1100 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'light') {
                            $condition = match (true) {
                                $value >= 50 && $value <= 100 => 'Moderate',
                                $value >= 101 && $value <= 1001 => 'Good',
                                $value >= 1001 && $value <= 30000 => 'Moderate',
                                default => 'Bad',
                            };
                        }elseif($key == 'sound') {
                            $condition = match (true) {
                                $value >= 1 && $value <= 2 => 'Good',
                                $value >= 3 && $value <= 4 => 'Moderate',
                                $value == 5 => 'Bad',
                                default => 'Bad',
                            };
                        }elseif($key == 'no2') {
                            $condition = match (true) {
                                $value >= 0 && $value <= 53 => 'Good',
                                $value >= 54 && $value <= 100 => 'Moderate',
                                $value >= 101 && $value <= 1000 => 'Bad',
                                default => 'Bad',
                                };
                        }elseif($key == 'ozone') {
                        $condition = match (true) {
                            $value >= 0 && $value <= 70 => 'Good',
                            $value >= 71 && $value <= 125 => 'Moderate',
                            $value >= 126 && $value <= 1000 => 'Bad',
                            default => 'Bad',
                        };
                    }else{
                       $condition='';
                    }
                   if($value==null && $key=='no2'){
                    $value=null;
                    $condition ='';
                   }
                   if($value==null && $key=='ozone'){
                    $value=null;
                    $condition ='';
                   }
                   
                    if ($exists) {
                               //compute the hr from 12am to 12pm
                               $startTime = '00:00';
                               $currentTime = now();
                               $startTimeObj = Carbon::createFromFormat('H:i', $startTime)->startOfDay();
   
                               $accumulatedMinutes =  $startTimeObj->diffInMinutes($currentTime);
                               $accumulatedHours = round($accumulatedMinutes / 60);
                               
                               $endOfDay = $startTimeObj->endOfDay();
                               $remainingMinutes = $currentTime->diffInMinutes($endOfDay);
                               $remainingHr = round($remainingMinutes / 60);
   
                               $currentTimeInMilliseconds = Carbon::now()->valueOf();

                        $Prev_Value = DB::table('uhoo_Device_Details')
                                        ->select('Value')
                                        ->where('Label',$key)
                                        ->first();

                    $currentTime = now();
                    $currentTimeInMilliseconds = $currentTime->timestamp * 1000;
                    
                    if ($currentTime->minute == 0) {//insert only if its exactly an hr
                      
                    }
                        DB::table('uhoo_Device_Details')
                        ->where('Serial_Number', $Serial)
                        ->where('Label',$key)
                        ->update(['Prev_Value' => $Prev_Value->Value,
                                   'Updated_At' => now()]); 

                        DB::table('uhoo_Device_Details')
                          ->where('Serial_Number', $Serial)
                          ->where('Label',$key)
                          ->update(['Value' => $value]);
                      
                        DB::table('uhoo_Device_Details')
                        ->where('Serial_Number', $Serial)
                        ->where('Label',$key)
                        ->update(['Condition' => $condition]);

                    } else {
                              $startTime = '00:00';
                              $currentTime = now();
                              $startTimeObj = Carbon::createFromFormat('H:i', $startTime)->startOfDay();
  
                              $accumulatedMinutes =  $startTimeObj->diffInMinutes($currentTime);
                              $accumulatedHours = round($accumulatedMinutes / 60);
                              
                              $endOfDay = $startTimeObj->endOfDay();
                              $remainingMinutes = $currentTime->diffInMinutes($endOfDay);
                              $remainingHr = round($remainingMinutes / 60);
  
                              $currentTimeInMilliseconds = Carbon::now()->valueOf();

                            DB::table('uhoo_Device_Details')->insert([
                                'Serial_Number' => $Serial,
                                'Label' => $key,
                                'Value' => $value,
                                'Condition' => $condition,//do if else here,
                                'Created_At'=>now(),
                                'Measurement' => array_key_exists($key, $Measurements) ? $Measurements[$key] : '',
                            ]); 

                            $currentTime = now();
                            $currentTimeInMilliseconds = $currentTime->timestamp * 1000;
                            
                            if ($currentTime->minute == 0) {
                              
                            }
                    }  
                }
                $ctr++;
        }
      //  dd($ar,$mac);
        return redirect('/uhooDashboard');
        }catch(\Throwable $e){
            return response()->json([
                'Error' =>'function uhooDeviceData - '.  $e->getMessage()
            ]);
        }
    }
    public function uhoo_all_device(){//list of all devices in wellness display with the cute icons
        
   
        $uhoo_devices =  DB::table('uhoo_device_header as a')
                        ->join('uhoo_device_details as b','a.Serial_Number','=','b.Serial_Number')
                        ->select('a.Serial_Number')
                        ->distinct()
                        ->get();
       
                        $Thewhoswhos = [];
                        foreach ($uhoo_devices as $uhoo_devices) {
                            $Condition = DB::table('uhoo_device_details as a')
                                        ->join('uhoo_device_header as b','a.Serial_Number','=','b.Serial_Number')
                                        ->select('a.Value','a.Label','a.Prev_Value','a.Condition','a.Measurement','b.Mac_Address','a.Serial_Number','b.Device_Name','b.Status')
                                        ->where('a.Serial_Number',$uhoo_devices->Serial_Number)
                                        ->where('a.Condition', '!=', '')
                                        ->distinct()
                                        ->get();
                            $Thewhoswhos[]= $Condition;
                        }

        
                        return response()->json([
                            'cond' =>$Thewhoswhos
                        ]);
        
        return response()->json([
            'uhoo_devices' =>$uhoo_devices
        ]);
    }
    public function uhoo_sort(Request $request){ //clicking the cards
       $color=$request->color;

        $GoodStatus =  DB::table('uhoo_device_header as a')
                        ->join('uhoo_device_details as b','a.Serial_Number','=','b.Serial_Number')
                        ->select('a.Serial_Number')
                        ->where('b.Condition',$color)
                        ->distinct()
                        ->get();
     
            
                        $Thewhoswhos = [];
                        foreach ($GoodStatus as $GoodStatus) {
                            $Condition = DB::table('uhoo_device_details as a')
                                        ->join('uhoo_device_header as b','a.Serial_Number','=','b.Serial_Number')
                                        ->select('a.Value','a.Label','a.Prev_Value','a.Condition','a.Measurement','a.Serial_Number','b.Device_Name','b.Status')
                                        ->where('a.Serial_Number',$GoodStatus->Serial_Number)
                                        ->where('a.Condition', '!=', '')
                                        ->distinct()
                                        ->get();
                            $Thewhoswhos[]= $Condition;
                        }

            $Devices = DB::table('uhoo_device_header as a')
                        ->join('uhoo_device_details as b','a.Serial_Number','=','b.Serial_Number')
                        ->select('a.Serial_Number')
                        ->get();
            $arr=[];
            $ar=[];
		$huh = collect();
            foreach($Devices as $Serial){
                $huh = DB::table('uhoo_device_details as a')
                            ->join('uhoo_device_header as b','a.Serial_Number','=','b.Serial_Number')
                            ->select('a.Value','a.Label','a.Prev_Value','a.Condition','a.Measurement','a.Serial_Number','b.Device_Name')
                            ->where('a.Serial_Number',$Serial->Serial_Number)
                            ->where('a.Condition', '!=', '')
                            ->whereIn('a.Condition',["Bad","Moderate"])
                            ->distinct()
                            ->get();
                    if($huh){
                        $arr=[];
                    }else{
                        $arr=$huh;
                    }
            }
                        return response()->json([
                            'cond' =>$Thewhoswhos,
                            'green' =>$arr
                        ]);
     
    }
    public function uhoo_days_in_month(Request $request){//chart render data
       try{

       
        $TheLabel = $request->Label;
        $TheSerial = $request->Serial_Number;
        //  dd($TheLabel,$TheSerial);
        $startOfMonth = Carbon::now()->startOfMonth(); // First day of the current month, 00:00:00
        $endOfMonth = Carbon::now()->endOfMonth();     // Last day of the current month, 23:59:59
        $DataOfTheDay = DB::table('hist_uhoo_device_details')
                        ->select('Serial_Number', 'Label', 'Value', 'Condition', 'Measurement', 'Created_At')
                        ->where('Serial_Number', $TheSerial)
                        ->whereIn('Label', [$TheLabel, 'timestamp']) // Corrected whereIn
                        ->whereBetween('Created_At', [$startOfMonth, $endOfMonth])
                        ->orderBy('Created_At')
                        ->get();
                     //  dd($DataOfTheDay);
                     $row = [];

                     $rows = [];

                     foreach ($DataOfTheDay as $Data) {
                         // Check if the row with timestamp is already set
                         if (!isset($row) || isset($row['timestamp'])) {
                             // Add the completed row to $rows if available
                             if (isset($row)) {
                                 $rows[] = $row;
                             }
                             // Start a new row
                             $row = [];
                         }
                     
                         if ($Data->Label == 'virusIndex') {
                             $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'moldIndex'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'temperature'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'humidity'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'pm25'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'tvoc'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'co2'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'co'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'airPressure'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'pm1'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'pm4'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'pm10'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'ch2o'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'light'){
                            $row['value'] = $Data->Value;
                         }elseif($Data->Label == 'sound'){
                            $row['value'] = $Data->Value;
                         }elseif ($Data->Label == 'timestamp') {
                             $row['timestamp'] = $Data->Value;
                         }
                     }
                     // Add the last row if it's not empty
                if (!empty($row)) {
                    $rows[] = $row;
                }

                // Wrap in "Data"
             //   $result = ['Data' => $rows];

            return response()->json([
                'Data'=>$rows,
            ]);
        }catch(\Throwable $e){
            dd($e->getMessage());
            \Log::error('uhoo_days_in_month', ['ERROR: ' => $e->getMessage()]);
        }
    }
    public function uhoo_create_history(){//Update data, update dashboard,insert to history
    
        if (Cache::add('uhoo_create_history_lock', true, 60)) {
            try{ 
             
                $UpdateData = $this->uhooDeviceData();
                $DataAccess = $this->uhooDeviceList();
                $accessToken = $this->uhooAccessCode(); 
                $DataConvert = json_decode($DataAccess->getContent(), true);
                $combined = array_combine($DataConvert['MacAddress'], $DataConvert['SerialNumber']);
                    foreach($combined as $Mac => $Serial){
                            $ctr = 0;
                            $url = 'https://api.uhooinc.com/v1/devicedata';
                            $dataa = [
                                        'macAddress' => $Mac, //$macAddress
                                        'mode' => 'minute' // Data mode
                                    ];
                            $response = Http::withHeaders([
                                        'Authorization' => 'Bearer ' . $accessToken
                                    ])->withoutVerifying()->asForm()->post($url, $dataa);
            
                            $dataa = $response->json();
                            $sensorData = $dataa['data'][$ctr];
                            $Measurements = $dataa['usersettings'];
                                
                            foreach ($sensorData as $key => $value){
                                $exists = DB::table('uhoo_Device_Details')
                                            ->where('Serial_Number', $Serial)
                                            ->where('Label', $key)
                                            ->exists();
                                
                                if ($key == 'virusIndex') {
                                    $condition = match (true) {
                                        $value <= 3 => 'Good',
                                        $value >= 4 && $value <= 8 => 'Moderate',
                                        default => 'Bad',
                                        
                                    };
                                    }else if($key == 'moldIndex') {
                                        $condition = match (true) {
                                            $value <= 3 => 'Good',
                                            $value >= 4 && $value <= 8 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if ($key == 'temperature') {
                                        $condition = match (true) {
                                            $value < 15 => 'Bad',
                                            $value <= 20 && $value >= 16 => 'Moderate',
                                            $value >= 21 && $value <= 26 => 'Good',
                                            default => 'Bad',
                                        };
                                    }elseif($key == 'humidity') {
                                        $condition = match (true) {
                                            $value >= 30 && $value <= 50 => 'Good', // Correct condition for Good
                                            $value > 50 && $value <= 90 => 'Moderate', // Moderate range after Good
                                            $value > 10 && $value < 30 => 'Moderate', // Lower range for Moderate
                                            default => 'Bad',
                                        };
                                    }else if($key == 'co2') {
                                        $condition = match (true) {
                                            $value >= 400 && $value <= 800 => 'Good',
                                            $value >= 801 && $value <= 1500 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'tvoc') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 660 => 'Good',
                                            $value >= 661 && $value <= 5500 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'ch2o') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 500 => 'Good',
                                            $value >= 501 && $value <= 750 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'pm1') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 12 => 'Good',
                                            $value >= 13 && $value <= 35 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'pm25') {
                                        $condition = match (true) {
                                            $value >= 0 && $value < 12 => 'Good',
                                            $value >= 12 && $value <= 35 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'pm4') {
                                        $condition = match (true) {
                                            $value >= 0 && $value < 12 => 'Good',
                                            $value >= 12 && $value <= 35 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'pm10') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 20 => 'Good',
                                            $value >= 21 && $value <= 150 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'co') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 9 => 'Good',
                                            $value >= 10 && $value <= 35 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'airPressure') {
                                        $condition = match (true) {
                                            $value >= 600 && $value <= 970 => 'Moderate',
                                            $value >= 971 && $value <= 1030 => 'Good',
                                            $value >= 1031 && $value <= 1100 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'light') {
                                        $condition = match (true) {
                                            $value >= 50 && $value <= 100 => 'Moderate',
                                            $value >= 101 && $value <= 1001 => 'Good',
                                            $value >= 1001 && $value <= 30000 => 'Moderate',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'sound') {
                                        $condition = match (true) {
                                            $value >= 1 && $value <= 2 => 'Good',
                                            $value >= 3 && $value <= 4 => 'Moderate',
                                            $value == 5 => 'Bad',
                                            default => 'Bad',
                                        };
                                    }else if($key == 'no2') {
                                        $condition = match (true) {
                                            $value >= 0 && $value <= 53 => 'Good',
                                            $value >= 54 && $value <= 100 => 'Moderate',
                                            $value >= 101 && $value <= 1000 => 'Bad',
                                            default => 'Bad',
                                            };
                                    }else if($key == 'ozone') {
                                    $condition = match (true) {
                                        $value >= 0 && $value <= 70 => 'Good',
                                        $value >= 71 && $value <= 125 => 'Moderate',
                                        $value >= 126 && $value <= 1000 => 'Bad',
                                        default => 'Bad',
                                    };
                                }else{
                                    $condition='';
                                }
                                if($value==null && $key=='no2'){
                                $value=null;
                                $condition ='';
                                }
                                if($value==null && $key=='ozone'){
                                $value=null;
                                $condition ='';
                                }
                                
                                    $startTime = '00:00';
                                    $currentTime = now();
                                    $startTimeObj = Carbon::createFromFormat('H:i', $startTime)->startOfDay();
            
                                    $accumulatedMinutes =  $startTimeObj->diffInMinutes($currentTime);
                                    $accumulatedHours = round($accumulatedMinutes / 60);
                                    
                                    $endOfDay = $startTimeObj->endOfDay();
                                    $remainingMinutes = $currentTime->diffInMinutes($endOfDay);
                                    $remainingHr = round($remainingMinutes / 60);
            
                                    $currentTimeInMilliseconds = Carbon::now()->valueOf();
                                    $currentTime = now();
                                //   $currentTimeInMilliseconds = $currentTime->timestamp * 1000;
                            DB::table('hist_uhoo_Device_Details')->insert([
                                'Serial_Number' => $Serial,
                                'Label' => $key,
                                'Value' => $value,
                                'Condition' => $condition,//do if else here,
                                'Created_At'=>now(),
                                'Measurement' => array_key_exists($key, $Measurements) ? $Measurements[$key] : '',
                                'hr' => $accumulatedHours,
                            ]); 
                    Log::info('Insert successful!');
                            }
                            $ctr++; 
                        }
                uhooCreateTicket::dispatch();
                        return response()->json([
                            'NewData'=>"Executed Successfully"
                        ]);
            }catch(\Throwable $e){
                \Log::error('uhoo_create_history', ['ERROR: ' => $e->getMessage()]);
                return response()->json([
                    'NewData'=>$e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'NewData'=>"uhoo_create_history skipped to prevent duplicate execution."
            ]);
            Log::info('uhoo_create_history skipped to prevent duplicate execution.');
        }
    
    }
    
    public function uhoo_update_val(Request $request){
        $Serial = $request->Serial;
        $NewData = DB::table('uhoo_device_details')
                    ->select('Serial_Number','Label','Value','Condition')
                    ->where('Serial_Number',$Serial)
                    ->get();
            return response()->json([
                'NewData'=>$NewData
            ]);
    }
    public function uhooUpdater(){

        $DataAccess = $this->uhooDeviceList();
        $accessToken = $this->uhooAccessCode(); 

        uhooUpdate::dispatch($DataAccess,$accessToken);

    }
    public function uhooTicketCreation(){

        try{
            uhooCreateTicket::dispatch();
        }catch(\Throwable $e){
            dd($e->getMessage());
        }
    }
    public function uhoo_access(){
        $auth = $this->authUser();

        return $uhooAccess =   DB::table('users as a')
        ->join('user_accesses as b','a.id','=','b.user_Id')
        ->join('company_profiles as c','c.Company_Id','=','b.Company_Id')
        ->join('company_profile_details as d','d.Company_Id','=','c.Company_Id')
        ->join('api_accounts as e','e.Api_Id','=','d.Api_Id')
        ->select('b.Company_Id')
        ->where('a.id',$auth->id)
        ->where('e.Platform','uhoo')
        ->get();


    }
// public function to be observe uhoo_create_history(){
        
    //   try{ 
        
    //     $DataAccess = $this->uhooDeviceList();
    //     $accessToken = $this->uhooAccessCode();   
    //     $combined = array_combine($DataAccess['MacAddress'], $DataAccess['SerialNumber']);

    //     foreach($combined as $Mac => $Serial){
    //             $ctr = 0;
    //             $url = 'https://api.uhooinc.com/v1/devicedata';
    //             $dataa = [
    //                         'macAddress' => $Mac, //$macAddress
    //                         'mode' => 'minute' // Data mode
    //                     ];
    //             $response = Http::withHeaders([
    //                         'Authorization' => 'Bearer ' . $accessToken
    //                     ])->asForm()->post($url, $dataa);

    //             $dataa = $response->json();

    //             dd($dataa);
    //             $sensorData = $dataa['data'][$ctr];
    //             $Measurements = $dataa['usersettings'];
                    
    //             foreach ($sensorData as $key => $value){
    //                 $exists = DB::table('uhoo_Device_Details')
    //                             ->where('Serial_Number', $Serial)
    //                             ->where('Label', $key)
    //                             ->exists();
                    
    //                 if ($key == 'virusIndex') {
    //                     $condition = match (true) {
    //                         $value <= 3 => 'Good',
    //                         $value >= 4 && $value <= 8 => 'Moderate',
    //                         default => 'Bad',
                            
    //                     };
    //                 }else if($key == 'moldIndex') {
    //                     $condition = match (true) {
    //                         $value <= 3 => 'Good',
    //                         $value >= 4 && $value <= 8 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if ($key == 'temperature') {
    //                     $condition = match (true) {
    //                         $value < 15 => 'Bad',
    //                         $value <= 20 && $value >= 16 => 'Moderate',
    //                         $value >= 21 && $value <= 26 => 'Good',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'humidity') {
    //                     $condition = match (true) {
    //                         $value < 15 => 'Bad',
    //                         $value <= 20 && $value >= 16 => 'Moderate',
    //                         $value >= 21 && $value <= 26 => 'Good',
    //                         $value >= 27 && $value <= 100 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'co2') {
    //                     $condition = match (true) {
    //                         $value >= 400 && $value <= 800 => 'Good',
    //                         $value >= 801 && $value <= 1500 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'tvoc') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 660 => 'Good',
    //                         $value >= 661 && $value <= 5500 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'ch2o') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 500 => 'Good',
    //                         $value >= 501 && $value <= 750 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'pm1') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 12 => 'Good',
    //                         $value >= 13 && $value <= 35 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'pm25') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 12 => 'Good',
    //                         $value >= 13 && $value <= 35 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'pm4') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 12 => 'Good',
    //                         $value >= 13 && $value <= 35 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'pm10') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 20 => 'Good',
    //                         $value >= 21 && $value <= 150 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'co') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 9 => 'Good',
    //                         $value >= 10 && $value <= 35 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'airPressure') {
    //                     $condition = match (true) {
    //                         $value >= 600 && $value <= 970 => 'Moderate',
    //                         $value >= 971 && $value <= 1030 => 'Good',
    //                         $value >= 1031 && $value <= 1100 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'light') {
    //                     $condition = match (true) {
    //                         $value >= 50 && $value <= 100 => 'Moderate',
    //                         $value >= 101 && $value <= 1001 => 'Good',
    //                         $value >= 1001 && $value <= 30000 => 'Moderate',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'sound') {
    //                     $condition = match (true) {
    //                         $value >= 1 && $value <= 2 => 'Good',
    //                         $value >= 3 && $value <= 4 => 'Moderate',
    //                         $value == 5 => 'Bad',
    //                         default => 'Bad',
    //                     };
    //                 }else if($key == 'no2') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 53 => 'Good',
    //                         $value >= 54 && $value <= 100 => 'Moderate',
    //                         $value >= 101 && $value <= 1000 => 'Bad',
    //                         default => 'Bad',
    //                         };
    //                 }else if($key == 'ozone') {
    //                     $condition = match (true) {
    //                         $value >= 0 && $value <= 70 => 'Good',
    //                         $value >= 71 && $value <= 125 => 'Moderate',
    //                         $value >= 126 && $value <= 1000 => 'Bad',
    //                         default => 'Bad',
    //                     };
    //                 }else{
    //                     $condition='';
    //                 }
    //                 if($value==null && $key=='no2'){
    //                 $value=null;
    //                 $condition ='';
    //                 }
    //                 if($value==null && $key=='ozone'){
    //                 $value=null;
    //                 $condition ='';
    //                 }
                   
    //                     $startTime = '00:00';
    //                     $currentTime = now();
    //                     $startTimeObj = Carbon::createFromFormat('H:i', $startTime)->startOfDay();

    //                     $accumulatedMinutes =  $startTimeObj->diffInMinutes($currentTime);
    //                     $accumulatedHours = round($accumulatedMinutes / 60);
                        
    //                     $endOfDay = $startTimeObj->endOfDay();
    //                     $remainingMinutes = $currentTime->diffInMinutes($endOfDay);
    //                     $remainingHr = round($remainingMinutes / 60);

    //                     $currentTimeInMilliseconds = Carbon::now()->valueOf();
    //                     $currentTime = now();
    //                     $currentTimeInMilliseconds = $currentTime->timestamp * 1000;
    //                     DB::table('hist_uhoo_Device_Details')->insert([
    //                         'Serial_Number' => $Serial,
    //                         'Label' => $key,
    //                         'Value' => $value,
    //                         'Condition' => $condition,//do if else here,
    //                         'Created_At'=>now(),
    //                         'Measurement' => array_key_exists($key, $Measurements) ? $Measurements[$key] : '',
    //                         'hr' => $accumulatedHours,
    //                     ]); 
    //                     Log::info('Insert successful!');
    //             }
    //             $ctr++; 
    //         }
    //     }catch(\Throwable $e){
    //         \Log::error('huh', ['exception' => $e->getMessage()]);

    //         // Log::error('Error in uhoo_create_history method: ' . $e->getMessage());
    //         // $this->error('An error occurred: ' . $e->getMessage());
            
    //     }
    // }
    
}
