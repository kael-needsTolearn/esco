<?php

namespace App\Http\Controllers;

use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\DeviceRoom;
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


class HomeController extends Controller
{
    public function index()
    {
        // if authenticated redirect to dashboard
        $user = Auth::user();

        if ($user) {
            return redirect('/dashboard');
        }
        // if unathenticated redirect to / or login 
        return redirect('/login');
    }
    public function resetPassword(Request $request){
    $email = ($request->emailaddress);
    //Mail::send()
    return view('auth.forgot-password');

    }
    public function updatePassword(){
        
    }
    public function dashboard()
    {
        $user = Auth::user();
        if ($user->usertype != 0) {
            // $online = Device::where('Status', 'online')->count();
            // $offline = Device::where('Status', 'offline')->count();
            $online = 0;
            $offline = 0;
            $rooms = DeviceRoom::all();
            $regions = CompanyProfile::all();
            return view('admin.dashboard.home', compact('online', 'offline', 'rooms', 'regions'));
        } else {
            $accesses = UserAccess::where('User_Id', $user->id)->get();
            $devices = [];
            $rooms = [];
            $online = 0;
            $offline = 0;
            $regions = [];
            $companies = [];
            $userAccesses = UserAccess::where('User_Id', $user->id)->get();
            if ($userAccesses) {
                foreach ($userAccesses as $userAccess) {
                    $company = CompanyProfileDetails::where('Company_Id', $userAccess->Company_Id)->first();
                    $companies[] = $company;
                }
                if (!empty($companies)) {
                    foreach ($companies as $company) {
                        $region = CompanyProfile::where('Company_Id', $company->Company_Id)->get();
                        if ($region) {
                            foreach ($region as $item) {
                                $regions[] = $item;
                            }
                        }
                    }
                    // dd($regions);
                }
            }

            if ($accesses->count() > 0) {
                foreach ($accesses as $access) {
                    $cpds = CompanyProfileDetails::where('Company_Id', $access->Company_Id)->get();
                    if ($cpds->count() > 0) {
                        foreach ($cpds as $cpd) {
                            $api = ApiAccount::where('Api_Id', $cpd->Api_Id)->first();
                            if ($api) {
                                $devices[$api->Api_Id]['online'] = Device::where('Api_Id', $api->Api_Id)
                                    ->where('Status', 'online')
                                    ->count();
                                $devices[$api->Api_Id]['offline'] = Device::where('Api_Id', $api->Api_Id)
                                    ->where('Status', 'offline')
                                    ->count();
                                // $online += $devices[$api->Api_Id]['online'];
                                // $offline += $devices[$api->Api_Id]['offline'];

                                $rooms = DeviceRoom::where('Api_Id', $api->Api_Id)->get();
                            }
                        }
                    }
                }
            }
            return view('admin.dashboard.home', compact('devices', 'online', 'offline', 'rooms', 'regions'));
        }
    }
    public function refreshDevice()
    {
        try{
        $user = Auth::user();
        if ($user->usertype != 0) {
            $apis = ApiAccount::all();
            foreach ($apis as $api) {
                if ($api->Platform == 'xio') {
                    $subscriptionKey = Crypt::decryptString($api->Variable1);
                    $accountId = Crypt::decryptString($api->Variable2);
                    // $xioRooms = DeviceRoom::all();
                    $url = "https://api.crestron.io/api/v1/device/accountid/{$accountId}/devices";   //get all devices
                    // $url = "https://api.crestron.io/api/v1/group/accountid/{$accountId}/groups";   //get all devices
                    $response = Http::withHeaders([
                        'XiO-subscription-key' => $subscriptionKey,
                    ])->withOptions([
                        'verify' => false // Disable SSL verification
                    ])->get($url);
                    // dd($response->json());
                    if ($response->successful()) {
                        $data = $response->json();
                        foreach ($data as $item) {
                            // Retrieve existing device
                            $existingDevice = Device::where('Device_Id', $item['device-cid'])->orderBy('Device_Name', 'asc')->first();
                            // Update existing device attributes if necessary
                            if ($existingDevice) {
                                $room = DeviceRoom::where('DeviceRoomID', $item['device-groupid'])->first();
                                $itemDeviceStatus = $item['device-status'];
                                // Check if the status is different before updating and saving history
                                // Update device fields and save
                                if ($existingDevice->Device_Loc !== $room->DeviceRoomLocation) {
                                    $existingDevice->Device_Loc = $room->DeviceRoomLocation;
                                }
                                if ($existingDevice->Room_Type !== $room->DeviceRoomName) {
                                    $existingDevice->Room_Type = $room->DeviceRoomName;
                                }
                                if ($existingDevice->Status !== $itemDeviceStatus) {

                                    $history = new DeviceHistory();
                                    $history->Device_ID = $item['device-cid'];
                                    $history->Device_Name = $item['device-name'];
                                    $history->Device_Desc = $item['device-category'];
                                    $history->Device_Loc = $room->DeviceRoomLocation;
                                    $history->Room_Type = $room->DeviceRoomName;
                                    $history->Manufacturer = $item['device-manufacturer'];
                                    $history->Serial_Num = $item['serial-number'];
                                    $history->MAC_Add = substr($item['device-cid'], 0, -3);
                                    $history->Status = $existingDevice->Status;
                                    $history->Previous_Date = $existingDevice->updated_at;
                                    $history->save();
                                    // $this->createTicket();
                                    // Update Status
                                    $existingDevice->Status = $itemDeviceStatus;
                                }
                                $existingDevice->save();
                            } else {
                                $room = DeviceRoom::where('DeviceRoomID', $item['device-groupid'])->first();
                                // Create new device if not exists
                                $device = new Device();
                                $device->Device_Id = $item['device-cid'];
                                $device->Device_Name = $item['device-name'];
                                $device->DeviceRoomID     = $item['device-groupid'];
                                $device->Device_Desc = $item['device-category'];
                                $device->Device_Loc = $room->DeviceRoomLocation;
                                $device->Room_Type = $room->DeviceRoomName;
                                $device->Manufacturer = $item['device-manufacturer'];
                                $device->Serial_Number = $item['serial-number'];
                                $device->Mac_Address = substr($item['device-cid'], 0, -3);
                                $device->Status = $item['device-status'];
                                $device->Api_Id = $api->Api_Id;
                                $device->save();
                                // }
                            }
                        }
                    }
                    // to delete
                    else {
                        return;
                        // response()->json([
                        //     'failed' => $response['message']
                        // ]);
                    }
                }
                if ($api->Platform == 'qsys') {
                    $bearerKey = Crypt::decryptString($api->Variable3);
                    // dd($bearerKey);
                    $url = "https://reflect.qsc.com/api/public/v0/cores";
                    // $url = "https://reflect.qsc.com/api/public/v0/systems";
                    // $url = "https://reflect.qsc.com/api/public/v0/systems/20573/items";

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $bearerKey,
                    ])->get($url);
                    if ($response->successful()) {
                        $data = $response->json();

                        foreach ($data as $item) {
                            // Retrieve existing device
                            $existingDevice = Device::where('Device_Id', $item['id'])->orderBy('Device_Name', 'asc')->first();
                            // Update existing device attributes if necessary
                            if ($existingDevice) {
                                $itemDeviceStatus = $item['status']['message'];
                                // Check if the status is different before updating and saving history
                                // Update device fields and save
                                if ($existingDevice->Device_Loc !== "") {
                                    $existingDevice->Device_Loc = "";
                                }
                                if ($existingDevice->Room_Type !== "") {
                                    $existingDevice->Room_Type = "";
                                }
                                if ($existingDevice->Status !== $itemDeviceStatus) {
                                    // Update Status
                                    $existingDevice->Status = $itemDeviceStatus;
                                }
                                $existingDevice->save();
                            } else {
                                // Create new device if not exists
                                $device = new Device();
                                $device->Device_Id = $item['id'];
                                $device->Device_Name = $item['name'];
                                $device->Device_Desc = $item['model'];
                                $device->Device_Loc = "";
                                $device->Room_Type = "";
                                $device->Manufacturer = "QSC";
                                $device->Serial_Number = $item['serial'];
                                $device->Mac_Address = "";
                                $device->Status = $item['status']['message'];
                                $device->Api_Id = $api->Api_Id;
                                $device->save();
                            }
                        }
                    }
                }
            }
        }

        return;
        }catch(\Throwable $e){
            dd($e->getMessage());
            return response()->json([
                'error'=>'function refreshDeviceWithParam - '.$e->getMessage()
            ]);
        }
    }
    public function refreshDeviceWithParam(Request $request)
    {
        try{
            $user = Auth::user();
            if ($user->usertype != 0) {
                $this->refreshDevice();
            }
                
                $RoomIds = $request->SelValues;
                $Country = $request->gCountry;
                $OrgId = $request->gOrgId;

                $Devices = DB::table('devices')
                    ->Leftjoin('device_rooms', 'devices.DeviceRoomID', '=', 'device_rooms.DeviceRoomID')
                    ->select('devices.*', 'device_rooms.DeviceRoomLocation')
                    ->whereIn('devices.DeviceRoomID', $RoomIds)
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
                dd($e->getMessage());
                return response()->json([
                    'error'=>'function refreshDeviceWithParam - '.$e->getMessage()
                ]);
            }
    }
    public function InitReports(Request $request)
    {
        try{
                $user = Auth::user();
                $reports = [];
                $Notifications = Device::all();
                $reports[] = $Notifications;
                if ($user->usertype == 0) {
                    // is a user ID used in accesses ID $user->id
                    $uniqid = UserAccess::where('User_Id', $user->id)->value('User_Id');


                    $RegionOrgs =  CompanyProfile::select('company_profiles.Country', 'company_profiles.Company_Id', 'company_profiles.Company_Name')
                        ->join('company_profile_details as B', 'company_profiles.Company_Id', '=', 'B.Company_Id')
                        ->join('user_accesses as C', 'B.Company_Id', '=', 'C.Company_Id')
                        ->where('C.User_Id', $uniqid)
                        ->distinct()
                        ->get();

                    $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                        ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                        ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                        ->select('devices.*')
                        ->distinct()
                        ->get();


                    // Get user accesses
                    $accesses = UserAccess::where('User_Id', $user->id)->get();
                    // If user has accesses, proceed
                    if ($accesses->isNotEmpty()) {
                        foreach ($accesses as $access) {
                            // Get company profile details
                            $cpds = CompanyProfileDetails::where('Company_Id', $access->Company_Id)->get();

                            // If company profile details exist, get reports
                            if ($cpds->isNotEmpty()) {
                                foreach ($cpds as $cpd) {
                                    $reports[] = Device::where('Api_Id', $cpd->Api_Id)->get();
                                }
                            }
                        }
                    }
                    // Pass reports to the view
                    return view('admin.dashboard.reports', compact('reports', 'RegionOrgs', 'Notifications', 'uniqid', 'Devices'));
                } else {
                    $uniqid = UserAccess::where('User_Id', $user->id)->value('User_Id');
                    $RegionOrgs = CompanyProfile::all()->unique('Country');
                    $Devices = Device::all();
                    return view('admin.dashboard.reports', compact('reports', 'RegionOrgs', 'Notifications', 'uniqid', 'Devices'));
                }
            }catch(\Throwable $e){
                return response()->json([
                    'error'=>'function InitReports - '.$e->getMessage()
                ]);
            }
    }
    public function AlertNotification(Request $request)
    {
        try{
            $Organization = $request->gOrganization; //its value is the company ID
            $UserId = $request->gUserId;
            $Region = $request->gRegion; //Country

            if ($Organization) {
                $AlertNotif = DB::table('zoho_desks')
                    ->select('zoho_desks.*', 'company_profiles.Country', 'devices.Device_Name', DB::raw('DATE_FORMAT(zoho_desks.created_at, "%M %d, %Y %l:%i%p") as created_at'))
                    ->join('company_profiles', 'zoho_desks.Company_Id', '=', 'company_profiles.Company_Id')
                    ->join('devices', 'devices.Device_Id', '=', 'zoho_desks.Device_Id')
                    ->where('zoho_desks.Status', '=', 'Open')
                    ->where('Country', '=', $Region)
                    ->where('company_profiles.Company_Id', '=', $Organization)
                    ->get();
            } else {
                $AlertNotif = DB::table('zoho_desks')
                    ->select('zoho_desks.*', 'company_profiles.Country', 'devices.Device_Name', DB::raw('DATE_FORMAT(zoho_desks.created_at, "%M %d, %Y %l:%i%p") as created_at'))
                    ->join('company_profiles', 'zoho_desks.Company_Id', '=', 'company_profiles.Company_Id')
                    ->join('devices', 'devices.Device_Id', '=', 'zoho_desks.Device_Id')
                    ->where('zoho_desks.Status', '=', 'Open')
                    ->where('Country', '=', $Region)
                    ->get();
            }
            return response()->json([
                'AlertNotif' => $AlertNotif,

            ]);
            }catch(\Throwable $e){
                return response()->json([
                    'error'=>'function AlertNotification - '.$e->getMessage()
                ]);
            }
    }
    public function FilterByRegion(Request $request)
    {
        $user = Auth::user();
        $Region = $request->Region;
        $UserId =  $request->gUserId;
        $gRegion = $request->gRegion;
        $Organization = $request->Organization;
        $Column = $request->val;
        // if($Organization){
        //     dd($Organization);
        // }
        $Organizations = CompanyProfile::where('Country', $Region)->get();
        if ($user->usertype == 0) {
            $companies = CompanyProfile::select('company_profiles.Country', 'company_profiles.Company_Id', 'company_profiles.Company_Name')
                ->join('company_profile_details as B', 'company_profiles.Company_Id', '=', 'B.Company_Id')
                ->join('user_accesses as C', 'B.Company_Id', '=', 'C.Company_Id')
                ->where('C.User_Id', $UserId)
                ->where('company_profiles.Country', $Region)
                ->get();
            //selecting region only
            $RegionOrg = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                ->where('user_accesses.User_Id', $UserId)
                ->where('company_profiles.Country', $gRegion)
                ->where('company_profiles.Company_Id', $Organization)
                ->select('devices.*')
                ->distinct()
                ->get();
            //   dd($Organization);
            $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                ->where('user_accesses.User_Id', $UserId)
                ->where('company_profiles.Country', $Region)
                ->select('devices.*')
                ->orderBy('devices.Device_Loc', 'asc') // Order by ascending device location
                ->distinct()
                ->get();
        } else {
            $RegionOrg = [];
            $Devices = [];
            $cps = CompanyProfile::where('Country', $Region)
                ->join('company_profile_details', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                ->join('api_accounts', 'company_profile_details.Api_Id', '=', 'api_accounts.Api_Id')
                ->join('devices', 'api_accounts.Api_Id', '=', 'devices.Api_Id')
                ->get(['devices.*']);
            $Devices = $cps;
            if ($Organization) {
                $cp = CompanyProfile::where('Country', $gRegion)
                    ->where('Company_Id', $Organization)
                    ->first();

                $cpds = CompanyProfileDetails::where('Company_Id', $cp->Company_Id)
                    ->get();
                if ($cpds) {
                    $apis = ApiAccount::join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                        ->where('company_profile_details.Company_Id', $cp->Company_Id)
                        ->get();
                    if ($apis) {
                        $devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                            ->whereIn('api_accounts.Api_Id', $apis->pluck('Api_Id')->toArray())
                            ->get();
                        $RegionOrg = $devices;
                    }
                }
            }
        }
        return response()->json([
            'data' => $Devices,
            'Org' => $Organizations,
            'RegionOrg' => $RegionOrg,

        ]);
    }
    public function AscDesc(Request $request)
    {
        $Region = $request->gRegion;
        $UserId =  $request->gUserId;
        $Organization = $request->gOrganization;
        $Column = $request->val;
        $ctr = $request->gCtr;
        $Devices = null;
        $DevicesDes = null;

        if ($UserId) {
            if ($ctr == 1) {
                $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                    ->where('user_accesses.User_Id', $UserId)
                    ->where('company_profiles.Country', $Region)
                    ->select('devices.*')
                    ->orderBy("devices.$Column", 'asc') // Order by ascending device location
                    ->distinct()
                    ->get();
            } else {
                //order by descending 
                $DevicesDes = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->join('user_accesses', 'company_profile_details.Company_Id', '=', 'user_accesses.Company_Id')
                    ->where('user_accesses.User_Id', $UserId)
                    ->where('company_profiles.Country', $Region)
                    ->select('devices.*')
                    ->orderBy("devices.$Column", 'desc') // Order by ascending device location
                    ->distinct()
                    ->get();
            }
        } else {
            if ($ctr == 1) {
                $Devices = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->where('company_profiles.Country', $Region)
                    ->select('devices.*')
                    ->orderBy("devices.$Column", 'asc') // Order by ascending device location
                    ->distinct()
                    ->get();
            } else {
                //order by descending 
                $DevicesDes = Device::join('api_accounts', 'devices.Api_Id', '=', 'api_accounts.Api_Id')
                    ->join('company_profile_details', 'api_accounts.Api_Id', '=', 'company_profile_details.Api_Id')
                    ->join('company_profiles', 'company_profiles.Company_Id', '=', 'company_profile_details.Company_Id')
                    ->where('company_profiles.Country', $Region)
                    ->select('devices.*')
                    ->orderBy("devices.$Column", 'desc') // Order by ascending device location
                    ->distinct()
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
    public function rooms(Request $request)
    {
        $rooms = $request->checkedRooms;
        $allOnline = 0;
        $allOffline = 0;
        $devices = [];

        // Check if $rooms is empty
        if (!empty($rooms)) {
            $devicesRooms = DB::table('devices')
                ->Leftjoin('device_rooms', 'devices.DeviceRoomID', '=', 'device_rooms.DeviceRoomID')
                ->select('devices.*', 'device_rooms.DeviceRoomLocation')
                ->whereIn('devices.DeviceRoomID', $rooms)
                ->orderBy('Device_Name', 'asc')
                ->get();
            foreach ($rooms as $room) {
                $on = Device::where('DeviceRoomID', $room)->where('status', 'online')->count();
                $off = Device::where('DeviceRoomID', $room)->where('status', 'offline')->count();

                //Device::whereIn('DeviceRoomID', $room)->orderBy('Device_Name', 'asc')->get();
                $allOnline += $on;
                $allOffline += $off;
                foreach ($devicesRooms as $device) {
                    $devices[] = $device;
                }
            }
            return response()->json([
                'online' => $allOnline,
                'offline' => $allOffline,
                'devices' => $devicesRooms,
            ]);
        } else {
            $user = Auth::user();
            if ($user->usertype != 0) {
                // $online = Device::where('Status', 'online')->count();
                // $offline = Device::where('Status', 'offline')->count();
                $online = '0';
                $offline = '0';
                return response()->json([
                    'online' => $online,
                    'offline' => $offline,
                ]);
            } else {
                $accesses = UserAccess::where('User_Id', $user->id)->get();
                $devices = [];
                $online = '0';
                $offline = '0';

                if ($accesses->count() > 0) {
                    foreach ($accesses as $access) {
                        $cpds = CompanyProfileDetails::where('Company_Id', $access->Company_Id)->get();
                        if ($cpds->count() > 0) {
                            foreach ($cpds as $cpd) {
                                $api = ApiAccount::where('Api_Id', $cpd->Api_Id)->first();
                                if ($api) {
                                    $devices[$api->Api_Id]['online'] = Device::where('Api_Id', $api->Api_Id)
                                        ->where('Status', 'online')
                                        ->count();
                                    $devices[$api->Api_Id]['offline'] = Device::where('Api_Id', $api->Api_Id)
                                        ->where('Status', 'offline')
                                        ->count();
                                    // $online += $devices[$api->Api_Id]['online'];
                                    // $offline += $devices[$api->Api_Id]['offline'];
                                }
                            }
                        }
                    }
                }
                return response()->json([
                    'online' => $online,
                    'offline' => $offline,
                ]);
            }
        }
    }
    public function getRegion(Request $request)
    {
        $country = $request->country;
        $user = Auth::user();
        $orgs = [];

        if ($user->usertype != 0) {
            // Fetch organizations based on the provided country
            $orgs = CompanyProfile::where('Country', $request->country)->get();
        } else {
            $companies = [];
            $userAccesses = UserAccess::where('User_Id', $user->id)->get();

            if ($userAccesses->isNotEmpty()) {
                foreach ($userAccesses as $userAccess) {
                    $company = CompanyProfileDetails::where('Company_Id', $userAccess->Company_Id)->first();
                    if ($company) {
                        $companies[] = $company;
                    }
                }

                foreach ($companies as $company) {
                    $region = CompanyProfile::where('Company_Id', $company->Company_Id)->first();
                    if ($region) {
                        $orgs[] = $region;
                    }
                }
            }
        }
        // If organizations are found
        if (!empty($orgs)) {
            $rooms = [];
            // Return JSON response with organizations and rooms
            return response()->json([
                'orgs' => $orgs,
                'rooms' => $rooms
            ]);
        }

        // If no organizations are found, return empty array
        return response()->json(['orgs' => []]);
    }
    public function getRooms(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->orgId;
        $rooms = [];
        $currentMonth = date('m');

        if ($orgId) {
            $cpds = CompanyProfileDetails::where('Company_Id', $orgId)->get();
            if (!$cpds->isEmpty()) {
                foreach ($cpds as $cpd) {
                    $apis = ApiAccount::where('Api_Id', $cpd->Api_Id)->get();

                    if (!$apis->isEmpty()) {
                        foreach ($apis as $api) {
                            $room = DeviceRoom::where('Api_Id', $api->Api_Id)->get();
                            if (!empty($room)) {
                                foreach ($room as $item) {
                                    $rooms[] = $item;
                                }
                            }
                        }
                    }
                }
            }

            $results = DB::table('device_histories')
                ->select('Device_Id')
                ->selectRaw('(((24*60) - TIMESTAMPDIFF(MINUTE, max(previous_date), max(created_at))) / (24*60)) * 100 as uptime_percentage')
                ->where('Status', '=', 'Offline')
                ->groupBy('Device_Id')
                ->get()
                ->toArray();
            //  dd($results);
            DB::statement('CREATE TEMPORARY TABLE TEMP_TABLE1 (
                    Device_Id VARCHAR(255) COLLATE utf8mb4_unicode_ci,
                    uptime_percentage VARCHAR(255)
                )');
        }

        $NotifCount = DB::table('zoho_desks')
            ->select(DB::raw('count(*) as Ticket_Count'))
            ->where('Status', '=', 'Open')
            ->where('Company_Id', '=', $orgId)
            ->get();

        $NewNotif = DB::table('zoho_desks')
            ->select(DB::raw('count(*) as Ticket_New'))
            ->where('Status', '=', 'Open')
            ->where('Company_Id', '=', $orgId)
            ->whereDate(DB::raw('DATE(created_at)'), '=', Carbon::today())
            ->get();



        // Return JSON response with organizations and rooms
        return response()->json([
            'rooms' => $rooms,
            'NotifCount' => $NotifCount
            // 'MostReliable' => $MostReliable,
            // 'LeastReliable' => $LeastReliable
        ]);
    }
    public function InitUptime(Request $request)
    {
        // $start_date = Carbon::now()->startOfDay();
        // $end_date = Carbon::now()->endOfDay();
        // if ($request->date) {
        $date_range =  $request->initialDateRange;
        // dd($date_range);
        // Parse the date range
        [$start_date, $end_date] = explode(' - ', $date_range);
        $start_date = Carbon::createFromFormat('m/d/Y', $start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('m/d/Y', $end_date)->endOfDay();
        // }

        // Convert dates to Carbon instances
        // // Calculate the total time in seconds
        // $totalTime = $start_date->diffInSeconds($end_date);
        // dd($start_date, $end_date, $totalTime);
        $RoomIDs = $request->selectedValues;
        $devices = [];

        // Retrieve devices for each RoomID
        if ($RoomIDs) {
            foreach ($RoomIDs as $RoomID) {
                $devices = array_merge($devices, Device::where('DeviceRoomID', $RoomID)->get()->all());
            }
        }

        $uptimeData = [];
        // $average = [];

        foreach ($devices as $device) {
            $offlineDuration = 0;

            // Retrieve history for the device
            $history = DeviceHistory::where('MAC_Add', $device->Mac_Address)
                ->where('Status', 'Offline')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('created_at')
                ->get();

            // Calculate offline duration for each incident
            foreach ($history as $index => $incident) {
                if ($incident) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d H:i:s', $incident->created_at)->format('Y-m-d H:i:s');
                    $offlineDuration += Carbon::parse($incident->Previous_Date)->diffInSeconds($formattedDate);
                }
            }

            // Calculate uptime percentage
            // Calculate the total time in seconds
            $totalTime = $start_date->diffInSeconds($end_date);
            $uptimePercentage = ($totalTime - $offlineDuration) / $totalTime * 100;
            $uptimeDuration = $totalTime - $offlineDuration;
            // $uptime = number_format($uptimePercentage, 2) . "%";
            // Check if uptime is greater than 0 or negative
            if ($uptimePercentage <= 0) {
                // If uptime is greater than 0 or negative, set uptime to 0%
                $uptime = 0 . "%";
            } else {
                // Otherwise, calculate uptime normally
                $uptime = number_format($uptimePercentage, 2) . "%";
            }

            // Populate uptime data
            $uptimeData[] = [
                "Device_Name" => $device->Device_Name,
                "Manufacturer" => $device->Manufacturer,
                "Room_Type" => $device->Room_Type,
                "Location" => $device->Device_Loc,
                "Serial_Number" => $device->Serial_Number,
                "Uptime" => $uptime,
                "percent" => $uptimePercentage,
                "Total_Duration" => $totalTime, // Optional: You might want to include this information
                "Offline_Duration" => $offlineDuration, // Optional: You might want to include this information
                "Online_Duration" => $uptimeDuration, // Optional: You might want to include this information
                "Incidents" => $history->count(),
            ];
        }
        // $percentage = 0;
        // foreach($uptimeData['percent'] as $item){
        //     $percentage += $item;
        // }

        return response()->json([
            'data' => $uptimeData,
            // 'percentage' => $percentage
        ]);
    }
    // public function getOfflineIncident(Request $request)
    // {
    //     $device_id = $request->device;
    //     $histories = DeviceHistory::where('MAC_Add', $device_id)->get();
    //     $offline_dates = [];
    //     $date_array = [];

    //     if ($histories->count() > 0) {
    //         foreach ($histories as $history) {
    //             if ($history->Status == "Online") {
    //                 $formatted_date = date("F j, Y", strtotime($history->created_at));
    //                 $date = $formatted_date;
    //                 $offline_dates[] = $date;
    //             }
    //         }
    //     }

    //     $HistoryOff = DB::table('device_histories')
    //         ->select('Previous_Date', 'created_at')
    //         ->where('Status', 'Offline')
    //         ->where('MAC_Add', $device_id)
    //         ->get();

    //     foreach ($HistoryOff as $Hist) {
    //         $start_date = Carbon::createFromFormat('Y-m-d H:i:s', $Hist->Previous_Date);
    //         $end_date = Carbon::createFromFormat('Y-m-d H:i:s', $Hist->created_at);

    //         // Add the date of created_at as well to include the last offline day
    //         $end_date->addDay();

    //         $current_date = $start_date->copy();
    //         while ($current_date->lte($end_date)) {
    //             $date_array[] = $current_date->format('F d, Y');
    //             $current_date->addDay();
    //         }
    //     }

    //     $historyOn = DB::table('device_histories')
    //         ->select('created_at')
    //         ->where('Status', 'Online')
    //         ->where('MAC_Add', $device_id)
    //         ->pluck('created_at')
    //         ->map(function ($createdAt) {
    //             return Carbon::parse($createdAt)->format('F d, Y');
    //         })
    //         ->toArray();

    //     $date_array = array_merge($date_array, $historyOn);

    //     return response()->json([
    //         'offline_dates' => $date_array
    //     ]);
    // }
    public function getOfflineIncident(Request $request)
    {
        $device_id = $request->device;
        $histories = DeviceHistory::where('MAC_Add', $device_id)->get();
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
                if ($history->Status == "Online") {
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
    public function DeleteCompanyProfile(Request $request)
    {
        $Company_Id = $request->gCompany_Id;

        try {
            CompanyProfile::where('Company_Id', $Company_Id)->delete();
            return response()->json([
                'success' => 'successs'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' =>'function DeleteCompanyProfile - '. $e->getMessage()
            ]);
        }
    }

    public function DeleteApiAccount(Request $request)
    {
        $Api_Id = $request->gApi_Id;

        try {
            ApiAccount::where('Api_Id', $Api_Id)->delete();

            return response()->json([
                'success' => 'successs'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' =>'function DeleteApiAccount - '.  $e->getMessage()
            ]);
        }
    }


    public function reliableRooms(Request $request)
    {
        try{
        $orgId = $request->orgId;
        $date_range =  $request->date_range;

        [$start_date, $end_date] = explode(' - ', $date_range);
        $start_date = Carbon::createFromFormat('m/d/Y', $start_date)->startOfDay();
        $end_date = Carbon::createFromFormat('m/d/Y', $end_date)->endOfDay();

        $rooms = [];
        if ($orgId) {
            $cpds = CompanyProfileDetails::where('Company_Id', $orgId)->get();
            if (!$cpds->isEmpty()) {
                foreach ($cpds as $cpd) {
                    $apis = ApiAccount::where('Api_Id', $cpd->Api_Id)->get();

                    if (!$apis->isEmpty()) {
                        foreach ($apis as $api) {
                            $room = DeviceRoom::where('Api_Id', $api->Api_Id)->get();
                            if (!empty($room)) {
                                foreach ($room as $item) {
                                    $rooms[] = $item;
                                }
                            }
                        }
                    }
                }
            }
        }
        $devices = [];

        // Retrieve devices for each RoomID
        foreach ($rooms as $room) {
            $devices = array_merge($devices, Device::where('DeviceRoomID', $room->DeviceRoomID)->get()->all());
        }
        $uptimeData = [];
        // $average = [];

        foreach ($devices as $device) {
            $offlineDuration = 0;

            // Retrieve history for the device
            $history = DeviceHistory::where('MAC_Add', $device->Mac_Address)
                ->where('Status', 'Offline')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('created_at')
                ->get();

            // Calculate offline duration for each incident
            foreach ($history as $index => $incident) {
                if ($incident) {
                    $formattedDate = Carbon::createFromFormat('Y-m-d H:i:s', $incident->created_at)->format('Y-m-d H:i:s');
                    $offlineDuration += Carbon::parse($incident->Previous_Date)->diffInSeconds($formattedDate);
                }
            }

            // Calculate uptime percentage
            // Calculate the total time in seconds
            $totalTime = $start_date->diffInSeconds($end_date);
            $uptimePercentage = ($totalTime - $offlineDuration) / $totalTime * 100;
            $uptimeDuration = $totalTime - $offlineDuration;
            // $uptime = number_format($uptimePercentage, 2) . "%";
            // Check if uptime is greater than 0 or negative
            if ($uptimePercentage < 0) {
                // If uptime is greater than 0 or negative, set uptime to 0%
                $uptime = number_format(0, 2) . "%";
            } else {
                // Otherwise, calculate uptime normally
                $uptime = number_format($uptimePercentage, 2) . "%";
            }

            // Populate uptime data
            $uptimeData[] = [
                "Room_ID" => $device->DeviceRoomID,
                "percent" => $uptimePercentage,
            ];
        }

        $roomArrays = [];

        foreach ($uptimeData as $item) {
            $roomId = $item['Room_ID'];
            $room = DeviceRoom::where('DeviceRoomID', $roomId)->first();
            $roomArrays[] = [
                "Room" => $room->DeviceRoomName,
                "Location" => $room->DeviceRoomLocation,
                "Percentage" => $item['percent']
            ];
        }

        // Initialize an array to store the sums and counts for each combination
        $averages = [];

        foreach ($roomArrays as $entry) {
            $room = $entry["Room"];
            $location = $entry["Location"];
            $percentage = $entry["Percentage"];

            // Check if this combination already exists in $averages
            $key = $room . '_' . $location;
            if (!isset($averages[$key])) {
                // If not, initialize the sum and count
                $averages[$key] = [
                    'sum' => 0,
                    'count' => 0
                ];
            }

            // Add the percentage to the sum and increment the count
            $averages[$key]['sum'] += $percentage;
            $averages[$key]['count']++;
        }

        // Calculate the average for each combination
        // Calculate the average for each combination
        $result = [];
        foreach ($averages as $key => $value) {
            $averagePercentage = $value['sum'] / $value['count'];
            $result[] = [
                'Room' => strtok($key, '_'),
                'Location' => substr($key, strpos($key, "_") + 1),
                'AveragePercentage' => $averagePercentage
            ];
        }

        // Sort the results by AveragePercentage in ascending order
        usort($result, function ($a, $b) {
            return $a['AveragePercentage'] <=> $b['AveragePercentage'];
        });

        // Limit the ascending sorted results to the top 10
        $ascendingSorted = array_slice($result, 0, 10);

        // Sort the results by AveragePercentage in descending order
        usort($result, function ($a, $b) {
            return $b['AveragePercentage'] <=> $a['AveragePercentage'];
        });

        // Limit the descending sorted results to the top 10
        $descendingSorted = array_slice($result, 0, 10);

        // Limit the results to the top 10
        // $desc_esult = array_slice($result, 0, 10);
        return response()->json([
            'desc' => $descendingSorted,
            'asc' => $ascendingSorted,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'function reliableRooms - '.$e->getMessage()
        ]);
    }
    }
    public function editCompanyProfile(Request $request)
    {
        try{
        $id = $request->CompanyID;

        $data = CompanyProfile::where('Company_id', $id)->first();
        return response()->json([
            'data' => $data
        ]);
        } catch (\Throwable $e) {
        return response()->json([
            'error' => 'function editCompanyProfile - '.$e->getMessage()
        ]);
    }
    }
    public function updateCompanyProfile(Request $request)
    {
        try{
        $id = $request->code_id;
        // dd($request->update_name);
        $data = SystemConfiguration::where('Code_ID', $id)->first();
        if ($data) {
            $data->Code_Name = $request->update_name;
            $data->Code_Description = $request->update_desc;
            $data->Code_Value = $request->update_value;
            $data->save();
        }

        return response()->json([
            'success' => "System Configuration Updated!"
        ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'function updateCompanyProfile - '.$e->getMessage()
            ]);
        }
    }
    public function RoomWellness(){
        
    }
}
