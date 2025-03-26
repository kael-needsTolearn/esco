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

class AdminController extends Controller
{
    // Dashboard
    public function reports()
    {
        return view('admin.dashboard.reports');
    }
    public function userAccount()
    {
        $users = User::all();
        return view('admin.system-config.user-account', compact('users'));
    }
    // System Configuration
    public function userAccess()
    {
        $ListOfEmailAdd = User::all();

        return view('admin.system-config.user-access', compact('ListOfEmailAdd'));
    }
    public function companyProfiles()
    {
        try{
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
       //dd($profiles);
        return view('admin.system-config.company-profile', compact('profiles', 'accounts'));
        } catch (\Throwable $e) {
            dd('function companyProfiles - '.$e->getMessage());
        }
    }
    public function addProfile(Request $request)
    {
        //  client
        $id = $request->Company_Id;
        try{
            if($id!=''){
                $companyProfile = CompanyProfile::where('Company_Id', $id)->first();
                $companyProfile->Company_Name = $request->Company_Name;
                $companyProfile->Company_Address = $request->Company_Address;
                $companyProfile->Country = $request->Country;
                $companyProfile->Contract_Name = $request->Contract_Name;
                $companyProfile->Contract_Start_Date = $request->Contract_Start_Date;
                $companyProfile->Contract_End_Date = $request->Contract_End_Date;
                $companyProfile->Account_Manager = $request->Account_Manager;
                $companyProfile->Account_Manager_Email = $request->Account_Manager_Email;

                $companyProfile->save();
                return response()->json([
                    'success' => 'Company Profile updated successfully!'
                ]);
            }else{

        $profile = new CompanyProfile();
        $profile->Company_Id = 'ESCO-' . Carbon::now()->timestamp;
        $profile->Company_Name = $request->Company_Name;
        $profile->Company_Address = $request->Company_Address;
        $profile->Country = $request->Country;
        $profile->Contract_Name = $request->Contract_Name;
        $profile->Contract_Start_Date = $request->Contract_Start_Date;
        $profile->Contract_End_Date = $request->Contract_End_Date;
        $profile->Account_Manager = $request->Account_Manager;
        $profile->Account_Manager_Email = $request->Account_Manager_Email;
        $profile->save();

        // admin
        return response()->json([
            'success' => 'Company Profile created successfully!'
        ]);
    } 
        }catch (\Throwable $e) {
          //  dd($e->getMessage());
            return response()->json([
                'error' => 'function addProfile - '.$e->getMessage()
            ]);
        }
    }
    public function apiAccounts()
    {
        // $profiles = CompanyProfile::all();
        $accounts = ApiAccount::all();
        return view('admin.system-config.account', compact('accounts'));
    }
    public function addApi(ApiRequest $request)
    {
        try{
        // Encrypting variables
        $v1 = Crypt::encryptString($request->variable1);
        $v2 = Crypt::encryptString($request->variable2);
        $v3 = Crypt::encryptString($request->variable3);

        // Decrypting existing values for comparison
        $xioAccounts = ApiAccount::where('Platform', 'xio')->get();
        $qsysAccounts = ApiAccount::where('Platform', 'qsys')->get();
        // dd($xioAccounts, $qsysAccounts);
        foreach ($xioAccounts as $xioAccount) {
            $decryptedV1 = Crypt::decryptString($xioAccount->Variable1);
            $decryptedV2 = Crypt::decryptString($xioAccount->Variable2);
            // dd($decryptedV1, $decryptedV2);
            if ($decryptedV1 === $request->variable1 || $decryptedV2 === $request->variable2) {
                return response()->json([
                    'error' => 'API account already exists!'
                ]);
            }
        }
        foreach ($qsysAccounts as $qsysAccount) {
            $decryptedV3 = Crypt::decryptString($qsysAccount->Variable3);
            if ($decryptedV3 === $request->variable3) {
                return response()->json([
                    'error' => 'API account already exists!'
                ]);
            }
        }
        // If no existing account, create a new one
        $api = new ApiAccount();
        $api->Platform = $request->Platform;
        $api->Description = $request->Description;
        if ($request->Platform === 'xio') {
            $api->Variable1 = $v1;
            $api->Variable2 = $v2;
        } elseif ($request->Platform === 'qsys') {
            $api->Variable3 = $v3;
        }
        $api->save();
        $latest = ApiAccount::where('Platform', 'xio')->latest()->first();

        $response = $this->getRoom($request->variable1, $request->variable2, $latest->Api_Id);

        if ($response === "Forbidden") {
            $latest->delete();
            return response()->json([
                'error' => "Account already expired!"
            ]);
        }

        if ($response !== "success") {
            $latest->delete();
            return response()->json([
                'error' => $response
            ]);
        }

        return response()->json([
            'success' => 'API account created!'
        ]);
         }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function addApi - '.$e->getMessage()
        ]);
        }
    }

    public function getRoom($variable1, $variable2, $Api_Id)
    {
        try{
        $room_url = "https://api.crestron.io/api/v1/group/accountid/{$variable2}/groups";  //Account Groups to get group id of isRoom = true
        $response = Http::withHeaders([
            'XiO-subscription-key' => $variable1,
        ])->withOptions([
            'verify' => false // Disable SSL verification
        ])->get($room_url);
        // dd($response->json());
        if ($response->successful()) {
            $rooms = $response->json();
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
                        $deviceRoom->Api_Id = $Api_Id;
                        $deviceRoom->DeviceRoomID = $room['id'];
                        $deviceRoom->DeviceRoomName = $room['Name'];

                        // Set room location if parent room exists
                        if (isset($parentRooms[$room['ParentGroupId']])) {
                            $deviceRoom->DeviceRoomLocation = $parentRooms[$room['ParentGroupId']];
                        } else {
                            $deviceRoom->DeviceRoomLocation = "NA";
                        }
                        $deviceRoom->save();
                    }
                }
            }
            return 'success';
        } else {
            $message = $response->json();
            return isset($message['Message']) ? $message['Message'] : (isset($message['message']) ? $message['message'] : 'Message not available');            
        }
    }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function getRoom - '.$e->getMessage()
        ]);
        }
        
    }
    public function SystemConfig()
    {
        return view('admin.system-config.SystemConfig');
    }
    public function ApiAccess()
    {
        //retrieval of options for email
        $options = CompanyProfile::all(); //use this in blade to show results

        return view('admin.system-config.ApiAccess', compact('options')); //this a blade name
    }
    public function AddApiAccess(Request $request) // also remove xD
    {
        try{
        $vals = $request->values;
        $vals1 = $request->values1;
        // dd($vals1);
        $CompanyId = $request->gCompanyId;
        $data = [];
        if ($vals) {
            foreach ($vals as $val) {
                $object = new companyProfileDetails(); //INSERT INTO DATABASE
                $object->Company_Id = $CompanyId;
                $object->Api_Id = $val;
                $object->save();
            }
        }
        if ($vals1) {
            foreach ($vals1 as $val1) {
                $object = CompanyProfileDetails::where('Api_Id', $val1)->where('Company_Id', $CompanyId);
                if ($object) {
                    $object->delete();
                }
            }
        }
        }   catch (\Throwable $e) {
        return response()->json([
            'error' => 'function getRoom - '.$e->getMessage()
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
        // dd($vals1);
        $EmailAddress = $request->gEmail;
        $user_id = User::where('email', $EmailAddress)->pluck('id')->first();

        if ($vals) {
            foreach ($vals as $val) {
                $object = new UserAccess(); //INSERT INTO DATABASE name of model
                $object->User_Id = $user_id;
                $object->Company_Id = $val;
                $object->save();
            }
        }
        if ($vals1) {
            foreach ($vals1 as $val1) {
                $object = UserAccess::where('User_Id', $user_id)->where('Company_Id', $val1);
                if ($object) {
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
    public function deleteUser(Request $request)
    {
        try{
        $id = $request->id;
        User::find($id)->delete();

        return response()->json([
            'deleted' => "User account deleted successfully!"
        ]);
    }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function SaveUserAccess - '.$e->getMessage()
        ]);
        }
    }

    public function addConfig(Request $request)
    {
        try{
        $data = $request->all();
        $config = new SystemConfiguration();
        $config->Code_Name = $data['code_name'];
        $config->Code_Value = $data['code_value'];
        $config->Code_Description = $data['code_desc'];
        $config->save();
        return response()->json([
            'success' => "System Configuration Created!"
        ]);
        }catch (\Throwable $e) {
        return response()->json([
            'error' => 'function SaveUserAccess - '.$e->getMessage()
        ]);
        }
    }
}
