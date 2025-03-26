<?php

namespace App\Http\Controllers;

use App\Models\SystemConfiguration;
use App\Models\CompanyProfile;
use App\Models\ApiAccount;
use App\Models\ZohoCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SystemConfig extends Controller
{
    public function SystemVal()
    {
        $user  = Auth::user();
        // if ($user->usertype != 0) {
        $Zoho_Code = DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_Code')
            ->value('CODE_VALUE');

        $Zoho_ClientId =  DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_ClientId')
            ->value('CODE_VALUE');

        $Zoho_ClientSecret = DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_ClientSecret')
            ->value('CODE_VALUE');

        $Zoho_OrgId = DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_OrgId')
            ->value('CODE_VALUE');

        $Zoho_DepartmentId = DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_DepartmentId')
            ->value('CODE_VALUE');

        $Zoho_ContactId =  DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_ContactId')
            ->value('CODE_VALUE');

        $Device_RefreshTime =  DB::table('system_configurations')
            ->where('Code_Name', 'Device_RefreshTime')
            ->value('CODE_VALUE');

        $Zoho_CreateTicketTimer = DB::table('system_configurations')
            ->where('Code_Name', 'Zoho_CreateTicketTimer')
            ->value('CODE_VALUE');

        $Crestron_ApiRefresh = DB::table('system_configurations')
            ->where('Code_Name', 'Crestron_ApiRefresh')
            ->value('CODE_VALUE');

        $data = [
            'rooms' => $Zoho_Code,
            'Zoho_ClientId' => $Zoho_ClientId,
            'Zoho_ClientSecret' => $Zoho_ClientSecret,
            'Zoho_OrgId' => $Zoho_OrgId,
            'Zoho_DepartmentId' => $Zoho_DepartmentId,
            'Zoho_ContactId' => $Zoho_ContactId,
            'Device_RefreshTime' => $Device_RefreshTime,
            'Zoho_CreateTicketTimer' => $Zoho_CreateTicketTimer,
            'Crestron_ApiRefresh' => $Crestron_ApiRefresh,
        ];
        // dd($data);
        return response()->json([
            'data' => $data
        ]);
        // }
        // return response()->json([
        //     'data' => []
        // ]);
    }

    public function editConfig(Request $request)
    {
        try{
        $id = $request->id;
        $data = SystemConfiguration::where('Code_ID', $id)->first();
        return response()->json([
            'data' => $data
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'function editConfig - '.$e->getMessage()
        ]);
    }
    }
    public function updateConfig(Request $request)
    {
        try{
        $id = $request->code_id;
      
        // dd($request->update_name);
        $data = SystemConfiguration::where('Code_ID', $id)->first();
        $cred = ZohoCredential::first();
        if ($data) {
            $value = $request->update_value;
            if ($data->Code_Name == "Device_RefreshTime" || $data->Code_Name == "Zoho_CreateTicketTimer" || $data->Code_Name == "Crestron_ApiRefresh") {
                if ($value < 2) {
                    return response()->json([
                        'error' => "Minimum value must be 2 minutes!"
                    ]);
                }
            }
            switch ($data->Code_Name) {
                case "Zoho_Code":
                    $cred->Code = $value;
                    break;
                case "Zoho_ClientId":
                    $cred->ClientID = $value;
                    break;
                case "Zoho_ClientSecret":
                    $cred->ClientSecret = $value;
                    break;
                case "Zoho_OrgId":
                    $cred->OrgID = $value;
                    break;
                case "Zoho_DepartmentId":
                    $cred->departmentID = $value;
                    break;
                case "Zoho_ContactId":
                    $cred->contactID = $value;
                    break;
            }
            $cred->save();
            $data->Code_Name = $request->update_name;
            $data->Code_Description = $request->update_desc;
            $data->Code_Value = $value;
            $data->save();
        }

        return response()->json([
            'success' => "System Configuration Updated!"
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'function updateConfig - '.$e->getMessage()
        ]);
    }
    }
}
