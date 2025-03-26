<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\DeviceRoom;
use App\Models\User;
use App\Jobs\LogJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Version2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId)
    {
        //
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = $this->userId;

        try {
            $user = DB::table('users as A')
                ->join('user_accesses as B', 'A.id', '=', 'B.User_Id')
                ->join('company_profile_details as C', 'B.Company_Id', '=', 'C.company_Id')
                ->select('B.Company_Id', 'A.usertype', 'C.Api_Id','A.First_Name','A.Last_Name')
                ->where('A.id', $userId)
                ->first();
                
            $apis = DB::table('users as A')
                ->join('user_accesses as B', 'A.id', '=', 'B.User_Id')
                ->join('company_profile_details as C', 'B.Company_Id', '=', 'C.company_Id')
                ->join('api_accounts as D', 'C.Api_Id', '=', 'D.Api_Id')
                ->select('D.*')
                ->where('A.id', $userId)
                ->get();
            foreach ($apis as $api) {
                if ($api->Platform == 'xio') {
                    $this->processXioPlatform2($api,$user);
                } elseif ($api->Platform == 'qsys') {
                    $this->processQsysPlatform2($api,$user);
                }
                
            }
        } catch (\Throwable $e) {
            \Log::error('Error in handle version2', [
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function processXioPlatform2($api,$user)
    {
	try{
        	$subscriptionKey = Crypt::decryptString($api->Variable1);
        	$accountId = Crypt::decryptString($api->Variable2);

	        $devicecids = DB::table('devices')
        	    ->select('*')
            	->where('Api_Id', $api->Api_Id)
        	    ->get();

      	  foreach ($devicecids as $devId) {
       	     $devicecid = $devId->Device_Id;
        	    $url = "https://api.crestron.io/api/v2/device/accountid/{$accountId}/devicecid/{$devicecid}/status";

         		   $response = Http::withHeaders([
           	     'XiO-subscription-key' => $subscriptionKey,
           	 ])->withOptions([
           	     'verify' => false,
           	 ])->get($url);

           	 $data = $response->json();
           	 if ($data && isset($data['device']) && isset($data['network'])) {
           	     $this->updateDeviceInfo($data['device'], $data['network'],$user);
           	   //  Log::Info('info', ['Itwas success' => $data['device'],$data['network'],$user]);
          	  } else {
           	     Log::warning('No Device/Network data returned', ['response' => $data]);
           	 }
       		}
 	} catch (\Throwable $e) {
            \Log::error('Error in handle XIOPlatform', [
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    private function processQsysPlatform2($api,$user)
    {
	try{
        $bearerKey = Crypt::decryptString($api->Variable3);
        $url = "https://reflect.qsc.com/api/public/v0/systems";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerKey,
        ])->withOptions([
            'verify' => false, // Disable SSL certificate verification
        ])->get($url);
        

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $system) {
                $this->handleQsysSystem($system, $bearerKey,$user);
            }
        } else {
            Log::error('Qsys API request failed', ['response' => $response->body()]);
        }
	} catch (\Throwable $e) {
            \Log::error('Error in handle QSYS', [
                'exception' => $e->getMessage()
            ]);
           // throw $e;
        }
    }
    private function handleQsysSystem($system, $bearerKey,$user)
    {
	try{

  //Log::info('Qsys API request', ['response' => $user]);
        $deviceRooms = DB::table('device_rooms')
            ->where('DeviceRoomID', $system['id'])
            ->first();
	
        if (is_null($deviceRooms)) {
            DB::table('device_rooms')->insert([
                'DeviceRoomID' => $system['id'],
                'DeviceRoomName' => $system['name'],
                'DeviceRoomLocation' => 'NA',
                'Api_Id' => $user->Api_Id,// Adjust if necessary
                'created_at' => now(),
                'updated_at' => null,
            ]);
        }

        $url = "https://reflect.qsc.com/api/public/v0/systems/{$system['id']}/items";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerKey,
        ])->withOptions([
            'verify' => false, // Disable SSL certificate verification
        ])->get($url);

        if ($response->successful()) {
            $deviceData = $response->json();
            foreach ($deviceData as $device) {
                $this->updateDeviceData($device,$user);
            }
        } else {
            Log::error('Qsys device data request failed', ['response' => $response->body()]);
        }
	} catch (\Throwable $e) {
            \Log::error('Error in handle QSYS system', [
                'exception' => $e->getMessage()
            ]);
           // throw $e;
        }
    }
    private function updateDeviceInfo($deviceInfo, $networkInfo,$user)
    {
	try{
        $existingDeviceRoom = DB::table('device_rooms')
                        ->select('*')
                        ->where('DeviceRoomID',$deviceInfo['device-groupid'])
                        ->first();
                       // Log::Info('info', ['roomid' => $existingDeviceRoom->DeviceRoomID]);    
        $existingDevice = DB::table('devices')
                            ->select('Device_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','MAC_Address','Status','created_at','updated_at')
                            ->where('Device_Id',$deviceInfo['device-cid'])
                            ->first();
                          //  Device::where('Device_Id', $deviceInfo['device-cid'])->first();
        if ($existingDevice) {
            if($deviceInfo['device-status']!=$existingDevice->Status){
              //  Log::Info('info', ['Itwas success' => $existingDevice->Device_Id]);
                DB::table('device_histories')->insert([
                    'Device_Id' => $existingDevice->Device_Id,
                    'Device_Name' => $existingDevice->Device_Name ?? '',
                    'Device_Desc' => $existingDevice->Device_Desc ?? '',
                    'Device_Loc' => $existingDevice->Device_Loc ?? '',
                    'Room_Type' => $existingDevice->Room_Type ?? '',
                    'Manufacturer' => $existingDevice->Manufacturer ?? '',
                    'Serial_Num' => $existingDevice->Serial_Number ?? '',
                    'IP_Address' => $existingDevice->IP_Address ?? '',
                    'MAC_Add' => $existingDevice->Mac_Address ?? '',
                    'Status' => $existingDevice->Status ?? '',
                    'Previous_Date' => $existingDevice->updated_at ?? $existingDevice->created_at,
                    'created_at' =>  DB::raw('NOW()'),
                    'updated_at' => null,
                ]);
              //  Log::Info('info', ['Itwas success' => 'Tangaaaa']);
                if (empty($deviceInfo['device-name'])) {
                    $deviceName = $deviceInfo['user-device-name']  ?? $deviceInfo['device-name'];
                   // Log::Info('info', ['Logging this ' => $deviceInfo['user-device-name'],$deviceInfo['device-name']]);
                } else {
                    $deviceName = $existingDevice->Device_Name ?? '';
                  //  Log::Info('info', ['Logging this ' => $existingDevice->Device_Name]);
                }
                DB::table('devices')
                ->where('Device_Id', $existingDevice->Device_Id)
                ->update([
                    'Device_Name' => $deviceName,
                    'DeviceRoomID' =>$existingDeviceRoom->DeviceRoomID,
                    'Device_Desc' => (!empty($deviceInfo['device-category']))? $deviceInfo['device-category'] : $existingDevice->Device_Desc,
                    'Device_Loc' => $existingDeviceRoom->DeviceRoomLocation ?? '',
                    'Room_Type' => $existingDeviceRoom->DeviceRoomName ?? '',
                    'Manufacturer' => (!empty($deviceInfo['device-manufacturer'])) ? $deviceInfo['device-manufacturer'] : $existingDevice->Manufacturer,
                    'Serial_Number' => (!empty($deviceInfo['serial-number']))? $deviceInfo['serial-number'] : $existingDevice->Serial_Number,
                    'IP_Address' => (!empty($networkInfo['nic-1-ip-address']))? $networkInfo['nic-1-ip-address'] : $existingDevice->IP_Address,
                    'MAC_Address' => (!empty($networkInfo['nic-1-mac-address']))? $networkInfo['nic-1-mac-address'] : $existingDevice->MAC_Address,
                    'Status' => $deviceInfo['device-status'] ?? 'Unknown',
                    'updated_at' =>DB::raw('NOW()'),
                ]);
               // Log::Info('User info ',  ['Record of qsys device' => $user] );
                LogJob::dispatch('','The Device '.$existingDevice->Device_Id.' Went '.$deviceInfo['device-status'] ?? 'Unknown'.'');

            }else{//changes system the info rather than status only
                if (empty($deviceInfo['device-name'])) {
                    $deviceName = $deviceInfo['user-device-name']  ?? $deviceInfo['device-name'];
                }else if(($deviceInfo['device-name'])){
                    $deviceName = $deviceInfo['device-name'];
                }else if(empty($deviceInfo['device-name']) && $deviceInfo['user-device-name']  ){
                    $deviceName = $deviceInfo['user-device-name'];
                }else {
                    $deviceName = $existingDevice->Device_Name ?? '';
                }

                if (empty($deviceInfo['device-category'])) {
                    $Device_Desc = $existingDevice->Device_Desc ?? '';
                }else {
                    $Device_Desc = $deviceInfo['device-category'] ?? $existingDevice->Device_Desc;
                }

                if (empty($deviceInfo['device-status'])){
                    $Device_Status = 'Offline';
                }else{
                    $Device_Status = $deviceInfo['device-status'];
                }
                DB::table('devices')
                ->where('Device_Id', $existingDevice->Device_Id)
                ->update([
                    'Device_Name' => $deviceName,
                    'DeviceRoomID' =>$existingDeviceRoom->DeviceRoomID,
                    'Device_Desc' => $Device_Desc,
                    'Device_Loc' => $existingDeviceRoom->DeviceRoomLocation ?? '',
                    'Room_Type' => $existingDeviceRoom->DeviceRoomName ?? '',
                    'Manufacturer' => (!empty($deviceInfo['device-manufacturer'])) ? $deviceInfo['device-manufacturer'] : $existingDevice->Manufacturer,
                    'Serial_Number' => (!empty($deviceInfo['serial-number']))? $deviceInfo['serial-number'] : $existingDevice->Serial_Number,
                    'IP_Address' => (!empty($networkInfo['nic-1-ip-address']))? $networkInfo['nic-1-ip-address'] : $existingDevice->IP_Address,
                    'MAC_Address' => (!empty($networkInfo['nic-1-mac-address']))? $networkInfo['nic-1-mac-address'] : $existingDevice->MAC_Address,
                   'Status' => $deviceInfo['device-status'],
                    'updated_at' =>DB::raw('NOW()'),
                ]);
              //  Log::Info('info', ['Logging this ' => $deviceInfo['device-status']]);
            }
        
        } else {
            // Log::warning('xio Device not found for update', ['device_id' => $deviceInfo['device-cid'],
            //                                             'device_name' => $deviceInfo['device-name']  ]);
        }
	} catch (\Throwable $e) {
            \Log::error('Error in handle Update Device Info', [
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    private function updateDeviceData($deviceData,$user)
    {
	try{
        //Log::Info('info', ['Record of qsys device' => $deviceData]);
        $existingDevice = DB::table('devices')
            ->where('Device_Id', $deviceData['id'])
            ->first();

        if ($existingDevice) {
            if ($existingDevice->Status != $deviceData['status']['message']) {
                DB::table('device_histories')->insert([
                    'Device_Id' => $existingDevice->Device_Id,
                    'Device_Name' => $existingDevice->Device_Name ?? '',
                    'Device_Desc' => $existingDevice->Device_Desc ?? '',
                    'Device_Loc' => $existingDevice->Device_Loc ?? '',
                    'Room_Type' => $existingDevice->Room_Type ?? '',
                    'Manufacturer' => $existingDevice->Manufacturer ?? '',
                    'Serial_Num' => $existingDevice->Serial_Number ?? '',
                    'IP_Address' => $existingDevice->IP_Address ?? '',
                    'MAC_Add' => $existingDevice->Mac_Address ?? '',
                    'Status' => $existingDevice->Status ?? '',
                    'Previous_Date' => $existingDevice->updated_at ?? $existingDevice->created_at,
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                DB::table('devices')
                    ->where('Device_Id', $existingDevice->Device_Id)
                    ->update([
                        'Status' => $deviceData['status']['message'],
                        'updated_at' => now(),
                    ]);
                
                  //  Log::Info('User info ',  ['Record of qsys device' => $user] );
               LogJob::dispatch('','The Device '.$existingDevice->Device_Id.' Went '.$deviceData['status']['message'] ?? 'Unknown'.'');

            }
        } else {
  		//Log::Info('info', ['Record of qsys device' => $deviceData]);
        }
	} catch (\Throwable $e) {
            \Log::error('Error in handle Update Device Info', [
                'exception' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

