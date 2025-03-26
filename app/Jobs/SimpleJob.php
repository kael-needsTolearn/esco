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
use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\DeviceHistory;
use App\Models\DeviceRoom;
use App\Models\ZohoDesk;
class SimpleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $userId;

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
                ->select('B.Company_Id', 'A.usertype', 'C.Api_Id')
                ->where('A.id', $userId)
                ->get();
                
            $apis = DB::table('users as A')
                ->join('user_accesses as B', 'A.id', '=', 'B.User_Id')
                ->join('company_profile_details as C', 'B.Company_Id', '=', 'C.company_Id')
                ->join('api_accounts as D', 'C.Api_Id', '=', 'D.Api_Id')
                ->select('D.*')
                ->where('A.id', $userId)
                ->get();
                \Log::error('Apis', ['exception' => $apis]);
            foreach ($apis as $api) {
                \Log::error('Apis', ['exception' => $api->Platform]);
                if ($api->Platform == 'xio') {
                    $this->processXioPlatform2($api);
                } elseif ($api->Platform == 'qsys') {
                    $this->processQsysPlatform2($api);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error in handle', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

        private function processXioPlatform2($api)
    {
        \Log::info('processXioPlatform2');
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
                $this->updateDeviceInfo($data['device'], $data['network']);
            } else {
                Log::warning('No Device/Network data returned', ['response' => $data]);
            }
        }
    }
    private function processQsysPlatform2($api)
    {
        $bearerKey = Crypt::decryptString($api->Variable3);
        $url = "https://reflect.qsc.com/api/public/v0/systems";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerKey,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $system) {
                $this->handleQsysSystem($system, $bearerKey);
            }
        } else {
            Log::error('Qsys API request failed', ['response' => $response->body()]);
        }
    }
    private function handleQsysSystem($system, $bearerKey)
    {
        $deviceRooms = DB::table('device_rooms')
            ->where('DeviceRoomID', $system['id'])
            ->first();

        if (is_null($deviceRooms)) {
            DB::table('device_rooms')->insert([
                'DeviceRoomID' => $system['id'],
                'DeviceRoomName' => $system['name'],
                'DeviceRoomLocation' => 'NA',
                'Api_Id' => $system['api_id'], // Adjust if necessary
                'created_at' => now(),
                'updated_at' => null,
            ]);
        }

        $url = "https://reflect.qsc.com/api/public/v0/systems/{$system['id']}/items";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerKey,
        ])->get($url);

        if ($response->successful()) {
            $deviceData = $response->json();
            foreach ($deviceData as $device) {
                $this->updateDeviceData($device);
            }
        } else {
            Log::error('Qsys device data request failed', ['response' => $response->body()]);
        }
    }
    private function updateDeviceInfo($deviceInfo, $networkInfo)
    {
        $existingDevice = Device::where('Device_Id', $deviceInfo['device-cid'])->first();
        if ($existingDevice) {
            $existingDevice->Device_Name = $deviceInfo['device-name'] ?? $deviceInfo['user-device-name'] ?? '';
            $existingDevice->DeviceRoomID = $deviceInfo['device-groupid'] ?? $existingDevice->DeviceRoomID;
            $existingDevice->Device_Desc = $deviceInfo['device-category'] ?? $existingDevice->Device_Desc;
            $existingDevice->Device_Loc = $existingDevice->Device_Loc ?? $existingDevice->DeviceRoomID;
            $existingDevice->Room_Type = $deviceInfo['device-groupid'] ?? $existingDevice->Room_Type;
            $existingDevice->Manufacturer = $deviceInfo['device-manufacturer'] ?? $existingDevice->Manufacturer;
            $existingDevice->Serial_Number = $deviceInfo['serial-number'] ?? $existingDevice->Serial_Number;
            $existingDevice->Status = $deviceInfo['device-status'] ?? $existingDevice->Status;
            $existingDevice->IP_Address = $networkInfo['nic-1-ip-address'] ?? $existingDevice->IP_Address;
            $existingDevice->MAC_Address = $networkInfo['nic-1-mac-address'] ?? $existingDevice->MAC_Address;
            $existingDevice->save();
        } else {
            Log::warning('xio Device not found for update', ['device_id' => $deviceInfo['device-cid'],
                                                        'device_name' => $deviceInfo['device-name']  ]);
        }
    }
    private function updateDeviceData($deviceData)
    {
        $existingDevice = DB::table('devices')
            ->where('Device_Id', $deviceData['id'])
            ->first();

        if ($existingDevice) {
            if ($existingDevice->Status != $deviceData['status']['message']) {
                DB::table('device_histories')->insert([
                    'Device_Id' => $existingDevice->Device_Id,
                    'Device_Name' => $existingDevice->Device_Name,
                    'Device_Desc' => $existingDevice->Device_Desc,
                    'Device_Loc' => $existingDevice->Device_Loc,
                    'Room_Type' => $existingDevice->Room_Type,
                    'Manufacturer' => $existingDevice->Manufacturer,
                    'Serial_Num' => $existingDevice->Serial_Number,
                    'IP_Address' => $existingDevice->IP_Address,
                    'MAC_Add' => $existingDevice->Mac_Address,
                    'Status' => $existingDevice->Status,
                    'Previous_Date' => $existingDevice->updated_at,
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                DB::table('devices')
                    ->where('Device_Id', $existingDevice->Device_Id)
                    ->update([
                        'Status' => $deviceData['status']['message'],
                        'updated_at' => now(),
                    ]);
            }
        } else {
            Log::warning('qsys Device not found for update', ['device_id' => $deviceData['id'],
                                                            'Device_Name' => $deviceData['name'],]);
        }
    }
}
