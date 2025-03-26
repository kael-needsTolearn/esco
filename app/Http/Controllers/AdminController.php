<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\DeviceRoom;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\ZohoDesk;
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
use App\Jobs\LoginNotifJob;
use App\Jobs\RefreshDeviceJob;
use App\Jobs\LogJob;
use Illuminate\Support\Facades\Log;
use Faker\Provider\ar_EG\Company;

use Illuminate\Support\Arr;
use App\Notifications\Notifications;
use App\Jobs\SlowJob;
use App\Jobs\SimpleJob;
use App\Jobs\Version2;
use App\Http\Requests\ValidateSession;
use App\Http\Controllers\SystemConfig;
use App\Services\MySharedService;
use Illuminate\Http\Client\RequestException;
use App\Jobs\uhooUpdate;
use App\Jobs\CreationOfTicketsJob;
use App\Jobs\uhooCreateTicket;
class AdminController extends Controller
{
    protected $authUser;

    public function __construct()
    {
        // Initialize the authenticated user
        $this->authUser = Auth::user();
    }

    public function authenticate(Request $request)
    {
       try {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $Auth = Auth::user();
            $request->session()->regenerate();

            $this->LoginAuth($Auth, $request);
            LogJob::dispatch($Auth, 'Login to the system');

            return view('auth.LoginEmailMessage');
        } else {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    } catch (\Throwable $e) {
        dd($e->getMessage());
        return abort(401, 'Link Expired');
    }
       
    }
    //Login Authentication

    public function LoginAuth(User $user,Request $request){
        try{
        $email = $user->email;   
        $token = Str::random(40);
 
        if($user){
           //null the token in the database
           DB::table('users')
           ->where('id', $user->id)
           ->update([
               'remember_token' =>null ,
               'updated_at' =>DB::raw('NOW()'),
           ]);
           // dd($token);
            $ForgetPassData = [
                'body'=>'You request to login, click "Here" to proceed the login.',
                'subject'=>'Login to ESCOCARE 360',
                'url' =>  url('LoginProceed'.$token),
                'ThankyouMessage'=>'Contact escocare360@esco.com.ph for further assistance.',
                'email'=>$user->email,
                'token'=>$token
            ];
            $ctr = 1;

            LoginNotifJob::dispatch($user,$ForgetPassData,$ctr);
    
            return view('auth.LoginEmailMessage');
         
        }else{
                return response()->json([
                    'message' => 'There is no existing email address in the database.'
                ]);
        }
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return abort(401, 'Link Expired');
        }
}
    public function LoginProceed(request $request,$token){
        try{
           // dd($token);
            $username = Auth::user();
            $email = Crypt::encryptString($username->email);
            $user = User::where('email', $username->email)->firstOrFail();
           // $user1 = User::where('email', $username->email)->firstOrFail();
            $user->update(['remember_token' => null]);
            $user->remember_token = $token;
            $user->save();
          
            $email = DB::table('users')
            ->select('email')
            ->where('remember_token',$user->remember_token)
            ->first();
            if($user->email){
                $Date = DB::table('users')
                        ->select('updated_at')
                        ->where('email',$user->email)
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
                    ->where('email', $email->email)
                    ->update(['remember_token' => null]);
                    Session::flush();
                   abort(401, 'Link Expired');
                } else {
			$userId = Auth::id();
			$latestSession = DB::table('sessions')
    			->where('user_id', $userId)
    			->orderBy('last_activity', 'desc')
    			->first();
			if ($latestSession) {
 			DB::table('sessions')
    			->where('user_id', $userId)
    			->whereNotIn('id', [$latestSession->id]) // Fetch sessions other than the latest one
    			->delete(); 
			}
                    return redirect('/dashboard');
                }
            }else{
		dd('Login Again');
            }
         }catch(\Throwable $e){
            dd($e->getMessage());
            abort(401, 'Link Expired');
           
         }
    }
    // Dashboard
    public function customDecryptFunction($encryptedData)
    {
        // Decrypt the data using Laravel's decrypt function
        return Crypt::decryptString($encryptedData);
    }
    public function reports()
    {
        $uhoo = $this->uhoo_access();
        return view('admin.dashboard.reports',compact('uhoo'));
    }
    public function userAccount()
    {
        $user = Auth::user();
        $auth = Auth::user();
        $users = User::all();
        //dd($users);
        $UserType = $user->usertype;
        $uhoo = $this->uhoo_access();

        return view('admin.system-config.user-account', compact('users','UserType','auth','uhoo'));
    }
    // System Configuration
    public function userAccess()
    {
        $auth = Auth::user();
        $ListOfEmailAdd = User::all();
        $uhoo = $this->uhoo_access();
        return view('admin.system-config.user-access', compact('ListOfEmailAdd','auth','uhoo'));
    }
    public function companyProfiles()
    {
        try{
        $auth = Auth::user();
        $client = new Client();
        $url = "https://desk.zoho.com/api/v1/profiles/6000000011303/agents?active=true";

        $response = Http::withHeaders([
            'orgId' => "680708905",
            'Authorization' => "Zoho-oauthtoken "
        ])->withOptions([
            'verify' => false // Disable SSL verification
        ])->get($url);
        $profiles = CompanyProfile::all();
        $accounts = ApiAccount::all();
        $uhoo = $this->uhoo_access();
       //dd($profiles);
        return view('admin.system-config.company-profile', compact('profiles', 'accounts','auth','uhoo'));
        } catch (\Throwable $e) {
         
            dd('function companyProfiles - '.$e->getMessage());
            abort(401, $e->getMessage());
        }
    }
    public function addProfile(Request $request)
    {
        $id = $request->Company_Id;
        try{
            if($id!=''){//UPDATE

                $companyProfile = CompanyProfile::where('Company_Id', $id)->first();

                if($companyProfile->Company_Name != strtoupper($request->Company_Name)){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.' from '.$companyProfile->Company_Name.' to '.strtoupper($request->Company_Name).' ');
                    $companyProfile->Company_Name = strtoupper($request->Company_Name);
                }
                if($companyProfile->Company_Address != strtoupper($request->Company_Address)){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.' from '.$companyProfile->Company_Address.' to '.strtoupper($request->Company_Address).' ');
                    $companyProfile->Company_Address = strtoupper($request->Company_Address);
                }
                if($companyProfile->Country != strtoupper($request->Country)){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Country.' to '.strtoupper($request->Country).' ');
                    $companyProfile->Country = strtoupper($request->Country);
                }
                if($companyProfile->Contract_Name != strtoupper($request->Contract_Name)){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Contract_Name.' to '.strtoupper($request->Contract_Name).' ');
                    $companyProfile->Contract_Name = strtoupper($request->Contract_Name);
                }
                if($companyProfile->Contract_Start_Date != $request->Contract_Start_Date){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Contract_Start_Date.' to '.$request->Contract_Start_Date.' ');
                    $companyProfile->Contract_Start_Date = $request->Contract_Start_Date;
                }
                if($companyProfile->Contract_End_Date != $request->Contract_End_Date){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Contract_End_Date.' to '.$request->Contract_End_Date.' ');
                    $companyProfile->Contract_End_Date = $request->Contract_End_Date;
                }
                if( $companyProfile->Account_Manager != strtoupper($request->Account_Manager)){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Account_Manager.' to '.strtoupper($request->Account_Manager).' ');
                    $companyProfile->Account_Manager = strtoupper($request->Account_Manager);
                }
                if($companyProfile->Account_Manager_Email != $request->Account_Manager_Email){
                    LogJob::dispatch($this->authUser,'Updated the company profile '.$id.'  from '.$companyProfile->Account_Manager_Email.' to '.$request->Account_Manager_Email.' ');
                    $companyProfile->Account_Manager_Email = $request->Account_Manager_Email;
                }
        
                $companyProfile->save();
                return response()->json([
                    'success' => 'Company Profile updated successfully!'
                ]);
            }else{
                $validated = $request->validate([
                    'Company_Name'=>'required|string|max:100',
                    'Company_Address'=>'required|string|max:100',
                    'Country'=>'required|string|max:100',
                    'Contract_Name'=>'required|string|max:100',
                    'Contract_Start_Date'=>'required|date',
                    'Contract_End_Date'=>'required|date',
                    'Account_Manager'=>'required|string|max:100',
                    'Account_Manager_Email'=>'required|email|max:100',
                ]);
            if($validated){//CREATE NEW COMPANY
                $profile = new CompanyProfile();
                $profile->Company_Id = 'ESCO-' . Carbon::now()->timestamp;
                $profile->Company_Name =strtoupper($request->Company_Name);
                $profile->Company_Address =strtoupper($request->Company_Address);
                $profile->Country =strtoupper($request->Country);
                $profile->Contract_Name = strtoupper($request->Contract_Name);
                $profile->Contract_Start_Date = $request->Contract_Start_Date;
                $profile->Contract_End_Date = $request->Contract_End_Date;
                $profile->Account_Manager = strtoupper($request->Account_Manager);
                $profile->Account_Manager_Email = $request->Account_Manager_Email;
                $profile->save();
                LogJob::dispatch($this->authUser,'Created new company profile.');
            // admin
            return response()->json([
                'success' => 'Company Profile created successfully!'
            ]);
            }else{
                return response()->json([
                    'error' => $validated,
                ]);
            }
                
    } 
        }catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
    public function apiAccounts()
    {
        // $profiles = CompanyProfile::all();
        $auth = Auth::user();
        $accounts = ApiAccount::all();
        $uhoo = $this->uhoo_access();
        return view('admin.system-config.account', compact('accounts','auth','uhoo'));
    }
    public function addApi(ApiRequest $request){
        try{
            //\Log::info('API::.',(['Api'=>$request]));

        // Encrypting variables
        //dd($request);
        $v1 = Crypt::encryptString($request->variable1);
        $v2 = Crypt::encryptString($request->variable2);
        $v3 = Crypt::encryptString($request->variable3);
        $Exists = false;
            if($request->Platform == 'qsys'){
                $qsysAccounts = ApiAccount::where('Platform', 'qsys')->get();
                foreach ($qsysAccounts as $qsysAccount) {
                    $decryptedV3 = Crypt::decryptString($qsysAccount->Variable3);
                    if ($decryptedV3 === $request->variable3) {
                       $Exists=true;
                       break;
                    }
                }
                if ($Exists==true) {
                    return response()->json([
                        'Error' => 'API account already exists!'
                    ]);
                }else{
                    $Api_Acc = DB::table('api_accounts')->insert([
                        "Platform"=>$request->Platform,
                        "Description"=>$request->Description,
                        "Variable3"=>$v3,
                    ]);
                    LogJob::dispatch($this->authUser,'Created new API.');
               
                    return response()->json(["Success"=>"Api created"]);
                }
            }else if ($request->Platform == 'xio'){
                $xioAccounts = ApiAccount::where('Platform', 'xio')->get();
            
                foreach ($xioAccounts as $xioAccount) {
                    $decryptedV1 = Crypt::decryptString($xioAccount->Variable1);
                    $decryptedV2 = Crypt::decryptString($xioAccount->Variable2);
                    if ($decryptedV1 === $request->variable1 || $decryptedV2 === $request->variable2) {
                        $Exists =true;
                        break;
                    }
                }
                if($Exists ==true){
                    return response()->json(["Error"=>"Api already Exists"]);
                }else{
                    $Api_Acc =  DB::table('api_accounts')->insert([
                        "Platform"=>$request->Platform,
                        "Description"=>$request->Description,
                        "Variable1"=>$v1,
                        "Variable2"=>$v2,
                        ]);
                        LogJob::dispatch($this->authUser,'Created new API.');
                        return response()->json(["Success"=>"Api created"]);
                }
            }else if ($request->Platform == 'uhoo'){
                $uhooAcc = ApiAccount::where('Platform', 'uhoo')->get();
                foreach ($uhooAcc as $uhooAcc) {
                    $decryptedV3 = Crypt::decryptString($uhooAcc->Variable3);
                    if ($decryptedV3 === $request->variable3) {
                       $Exists=true;
                       break;
                    }
                }
                if ($Exists==true) {
                    return response()->json([
                        'Error' => 'API account already exists!'
                    ]);
                }else{
                    $Api_Acc = DB::table('api_accounts')->insert([
                        "Platform"=>$request->Platform,
                        "Description"=>$request->Description,
                        "Variable3"=>$v3,
                    ]);
                    LogJob::dispatch($this->authUser,'Created new API.');
               
                    return response()->json(["Success"=>"Api created"]);
                }
            }
        }catch (\Throwable $e) {
          //  dd($e->getMessage());
            return response()->json([
                'Error' => 'function addApi - '.$e->getMessage()
            ]);
        }
    }
    public function SystemConfig()
    {
        $auth = Auth::User();
        $uhoo = $this->uhoo_access();

        return view('admin.system-config.SystemConfig',compact('auth','uhoo'));
    }
    public function ApiAccess()
    {
        //retrieval of options for email
        $auth = Auth::user();
        $options = CompanyProfile::all(); //use this in blade to show results
        $uhoo = $this->uhoo_access();
        return view('admin.system-config.ApiAccess', compact('options','auth','uhoo')); //this a blade name
    }
    public function AddApiAccess(Request $request) // also remove xD
    {
        try{
        $usertype = Auth::user();//fix this
        $vals = $request->values;
       // $combinedArray = $vals->pluck('Api_Id')->toArray();
        $vals1 = $request->values1;
        $CompanyId = $request->gCompanyId;
        $data = [];

         if (is_null($vals) || (is_array($vals) && empty($vals))) {
            // Handle the case where $vals is null or empty
            $ApiIdAssignedAlready = collect(); // or handle it as needed
            }else{
            $ApiIdAssignedAlready = DB::table('company_profile_details')
            ->select('*')
            ->whereIn('Api_Id', $vals)
            ->get();
         }
            if (!$ApiIdAssignedAlready->isEmpty()) { //this insert and delete api access
                return response()->json([
                    "error"=>"API already assigned to another company"
                ]);
                }else{
                if ($vals) { //INSERT INTO DATABASE
                    foreach ($vals as $val) {
                        $object = new companyProfileDetails(); 
                        $object->Company_Id = $CompanyId;
                        $object->Api_Id = $val;
                        $object->save();
                        LogJob::dispatch($this->authUser,'Created new company profile access '.$object->Company_Id.' with an API ID '.$val);
                    }
                }
                if ($vals1) { //Remove from DATABASE
                    foreach ($vals1 as $val1) {
                        $object = CompanyProfileDetails::where('Api_Id', $val1)
                        ->where('Company_Id', $CompanyId)
                        ->first(); // Use first() to get a single instance
                    if ($object) {
                            LogJob::dispatch($this->authUser,'Deleted a company profile access '.$object->Company_Id.' with an API ID '.$val1 );
                            CompanyProfileDetails::where('Api_Id', $val1)
                                                    ->where('Company_Id', $CompanyId)
                                                    ->delete(); // Perform delete operation
                        }
                    }
                }
                return response()->json([
                    "success"=>"Assigning API Successfully Saved"
                ]);
            }
        }   catch (\Throwable $e) {
        return response()->json([
            'error' => 'function Add API Access - '.$e->getMessage()
        ]);
        }
    }
    public function FetchApiAccess(Request $request)
    { //this is at controllers web.php
        try{
        $CompanyID = $request->email;
        //  //
        $apiAccounts =  ApiAccount::whereNotIn('Api_Id', function ($query) use ($CompanyID) {
            $query->select('Api_Id')
                ->from('company_profile_details')
                ->where('Company_Id', $CompanyID);
        })
            ->get();
        $apiAccountsHave = CompanyProfileDetails::select('company_profile_details.*', 'api_accounts.Platform', 'api_accounts.Description')
            ->join('api_accounts', 'company_profile_details.Api_Id', '=', 'api_accounts.Api_Id')
            ->where('company_profile_details.Company_Id', $CompanyID)
            ->get();
        $data = [
            "not" => $apiAccounts,
            "have" => $apiAccountsHave,
        ];
        return response()->json([
            'success' => $data
        ]);
        view('admin.system-config.ApiAccess', compact('ApiAccount'));
        }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function getRoom - '.$e->getMessage()
        ]);
        }
    }
    public function InitUserAccess(Request $request)
    {
        try{
        $EmailAddress = $request->email;
        $query = User::where('email', $EmailAddress)->first();
        $userId = $query->id;
        if ($query->count() == 0) {
            return response()->json([
                'Error' => 'No Email Address found'
            ]);
        } else {

            $companies = CompanyProfile::whereNotIn('company_profile_details.Company_Id', function ($query) use ($userId) {
                $query->select('Company_Id')
                    ->from('User_Accesses')
                    ->where('User_Id', $userId);
            })
                ->join('company_profile_details', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('api_accounts', 'company_profile_details.Api_Id', '=', 'api_accounts.Api_Id')
  		->select('company_profiles.Company_Id','company_profiles.Company_Name','company_profiles.Company_Address','company_profiles.Country','company_profiles.Account_Manager','company_profiles.Account_Manager_Email','company_profiles.Contract_Name')
		->distinct()
                ->get(); //CORRECT
            $companiesCes =   UserAccess::select('user_accesses.Company_Id', 'company_profiles.Company_Name', 'company_profiles.Country', 'company_profiles.Account_Manager', 'company_profiles.Contract_Name')
                ->join('company_profile_details', 'user_accesses.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('company_profiles', 'company_profile_details.Company_Id', '=', 'company_profiles.Company_Id')
                ->where('user_accesses.User_Id', '=', $userId)
                ->distinct()
                ->get();
            // UserAccess::select('user_accesses.*', 'company_profiles.Company_Name', 'company_profiles.Country','company_profiles.Account_Manager','company_profiles.Contract_Name')
            // ->join('company_profile_details', 'user_accesses.Company_Id', '=', 'company_profile_details.Company_Id')
            // ->where('user_accesses.User_Id', '=', $userId)
            // ->get();

            $data = [
                "not" => $companies,
                "have" => $companiesCes,
            ];
            return response()->json([
                'success' => $data
            ]);
            view('admin.system-config.ApiAccess', compact('user-access'));
        }
        }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function InitUserAccess - '.$e->getMessage()
        ]);
        }
    }
    public function SaveUserAccess(Request $request)
    {
        try{
        $vals = $request->values;
        $vals1 = $request->values1;
        $EmailAddress = $request->gEmail;
        $user_id = User::where('email', $EmailAddress)->pluck('id')->first();

        if ($vals) {
            foreach ($vals as $val) {
                $object = new UserAccess(); //INSERT INTO DATABASE name of model
                $object->User_Id = $user_id;
                $object->Company_Id = $val;
                $object->save();
                LogJob::dispatch($this->authUser,'Created a User access '.$user_id.' with a company Id '.$val );

            }
        }
        if ($vals1) {
            foreach ($vals1 as $val1) {
                $object = UserAccess::where('User_Id', $user_id)->where('Company_Id', $val1);
                if ($object) {
                    LogJob::dispatch($this->authUser,'Deleted a User access '.$user_id.' with a company Id '.$val1 );
                    $object->delete();
                }
            }
        }
        }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function SaveUserAccess - '.$e->getMessage()
        ]);
        }
    }
    public function deleteUser(Request $request)//reactivate-deactivate-update users
    {
        try{
        if($request->ctr ==2){
            $id = $request->id;
            User::find($id)->update(['Status' => 'active']);
            LogJob::dispatch($this->authUser,'Updated a user status from deactived to active');

            return response()->json([
                'reactivated' => "User account reactivated successfully!"
            ]);
        }else if ($request->ctr ==3){
            try{
            $id = $request->gId;
            $values=$request->values;
            $data = User::where('id', $id)->first();
            $emails = $request->email;
            if ($data) {
                if($data->First_Name !== $values[0]){
                    LogJob::dispatch($this->authUser,'Updated the user profile from '.$data->First_Name.' to '.$values[0].' ');
                    $data->First_Name = $values[0];
                }
                if($data->Last_Name !== $values[1]){
                    LogJob::dispatch($this->authUser,'Updated the user profile from '.$data->Last_Name.' to '.$values[1].' ');
                    $data->Last_Name = $values[1];
                }
                if($data->Position !== $values[3]){
                    LogJob::dispatch($this->authUser,'Updated the user profile from '.$data->Position.' to '.$values[3].' ');
                    $data->Position = $values[3];
                }
                if($data->email !== $emails){
                    LogJob::dispatch($this->authUser,'Updated the user profile from '.$data->email.' to '.$emails.' ');
                    $data->email = $emails;
                }
                if($data->usertype !== $values[4]){
                    LogJob::dispatch($this->authUser,'Updated the user profile from '.$data->usertype.' to '.$values[4].' ');
                    $data->usertype = $values[4];
                }   
                $data->save();
            }
            return response()->json([
                'success' => 'successfully updated the user'
            ]);
                }catch (\Throwable $e) {
                    return response()->json([
                        'error' => 'Error - '.$e->getMessage()
                    ]);
                    }
        }else if ($request->ctr == 4){
            $id = $request->id;

            $users = User::where('id', $id)->first();
           
            return response()->json([
                'users' => $users
            ]);
        }
        else{
        $id = $request->id;
        User::find($id)->update(['Status' => 'Deactivated']);
        LogJob::dispatch($this->authUser,'Deactived user '.$id.' ');

        DB::table('user_accesses')
        ->where('User_Id', $id)
        ->delete();
        LogJob::dispatch($this->authUser,'All of the user access '.$id.' are deleted automatically.');

        return response()->json([
            'deleted' => "User account deactivated successfully!"
        ]);
    }
    }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function deleteUser - '.$e->getMessage()
        ]);
        }
    }

    public function addConfig(Request $request)
    {
        try{
        $data = $request->all();
        $config = new SystemConfiguration();
        $config->Code_Name = $data['code_name'];
        if (isset($data['Company_Id']) && !empty($data['Company_Id'])) {
            $config->Company_Id = $data['Company_Id'];
        }else{
            $config->Company_Id = '';
        }
        $config->Code_Value = $data['code_value'];
        $config->Code_Description = $data['code_desc'];
        $config->save();
        LogJob::dispatch($this->authUser,'Created a system config');

        return response()->json([
            'success' => "System Configuration Created!"
        ]);
        }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function addConfig - '.$e->getMessage()
        ]);
        }
    }
    public function uhoo_access(){
        $auth = Auth::user();

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
   
}
