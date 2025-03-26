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
use App\Models\ApiAccount;
use App\Models\User;
use App\Jobs\LogJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RefreshDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    public $timeout = 120; // Set a timeout for the job
    private $AuthenticUserId;
    /**
     * Create a new job instance.
     */
    public function __construct($AuthenticUserId)
    {
        //
        $this->AuthenticUserId = $AuthenticUserId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
     $AuthenticUserId = $this->AuthenticUserId; 
        try{
            $arrayOfDevId=[];
           // $user = Auth::user();
           // if ($user->usertype != 0) {
                $apis = DB::table('users as a')
                        ->join('user_accesses as b','a.id','=','b.user_Id')
                        ->join('company_profiles as c','c.Company_Id','=','b.Company_Id')
                        ->join('company_profile_details as d','d.Company_Id','=','c.Company_Id')
                        ->join('api_accounts as e','e.Api_Id','=','d.Api_Id')
                        ->select('e.Api_Id','e.Platform','e.Variable1','e.Variable2','e.Variable3')
                        ->where('a.id',$AuthenticUserId)
                        ->get();

                //ApiAccount::all();
                foreach ($apis as $api) {
                    if ($api->Platform == 'xio') {
                       $xio = $this->xio($api);
                    }
                    else if($api->Platform == 'qsys'){
                        $qsys = $this->qsys($api,$AuthenticUserId);
                    }
                }
            }catch(\Throwable $e){
               // dd($e->getMessage());
               \Log::error('Error in handle Creation of Refresh Device', ['exception' => $e->getMessage()]);

            }
    }
    private function xio($api){
        $subscriptionKey = Crypt::decryptString($api->Variable1);
        $accountId = Crypt::decryptString($api->Variable2);

        $url = "https://api.crestron.io/api/v1/device/accountid/{$accountId}/devices";   //get all devices
        $response = Http::withHeaders([
            'XiO-subscription-key' => $subscriptionKey,
        ])->withOptions([
            'verify' => false // Disable SSL verification
        ])->get($url);
        \Log::warning('Sample Log ko ito ', ['exception' => $response]);
        $array[]=$response->json();
        if ($response->successful()) {
            $data = $response->json();
            
            foreach ($data as $item) {
                
                $arrayOfDevId[] = $item['device-cid'];
                $existingDevice = DB::table('devices')
                                ->select('Device_Id','Device_Name','DeviceRoomID','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Number','IP_Address','Mac_Address','Status','Api_Id','created_at','updated_at')
                                ->where('Device_Name', $item['device-name'])
                                ->where('DeviceRoomID',$item['device-groupid'])
                                ->orWhere('Device_Id',$item['device-cid'])
                                ->orderBy('Device_Name', 'asc')
                                ->first();

                $historyDevice = DB::table('device_histories')
                                ->select('Hist_Id','Device_ID','Device_Name','Device_Desc','Device_Loc','Room_Type','Manufacturer','Serial_Num','MAC_Add','Status','Previous_Date','created_at','updated_at')
                                ->where('Device_Id',$item['device-cid'])
                                ->get();

                $deviceRoomInfo = DB::table('device_rooms')
                                    ->select('DeviceRoomID','DeviceRoomName','DeviceRoomLocation')
                                    ->where('DeviceRoomID', $item['device-groupid'])
                                    ->first();
    
                $array[] = $existingDevice;
                if (is_null($existingDevice)) { //insert
                    $room = DeviceRoom::where('DeviceRoomID', $item['device-groupid'])->first();
                    $device = new Device();
                    $device->Device_Id = $item['device-cid'] ?? "";
                    $device->Device_Name = $item['device-name'] ?? "";
                    $device->DeviceRoomID = $room->DeviceRoomID ?? "";
                    $device->Device_Desc = $item['device-category'] ?? "";
                    $device->Device_Loc = $deviceRoomInfo->DeviceRoomLocation ?? "";        
                    $device->Room_Type = $deviceRoomInfo->DeviceRoomName ?? "";
                    $device->Manufacturer = $item['device-manufacturer'] ?? "";
                    $device->Serial_Number = $item['serial-number'] ?? "";
                    $device->Mac_Address = '';
                    $device->Status = $item['device-status'] ?? "Offline";
                    $device->IP_Address =  '';
                    $device->Api_Id = $api->Api_Id;
                    $device->created_at = now();
                    $device->updated_at = null;
                    $device->save();

                    LogJob::dispatch('','New Device inserted '.$item['device-cid'] .' - '.$item['device-name']  ?? 'Unknown'.'');
                }  
            }
            $arrayNotDevId = DB::table('devices')
                            ->whereNotIn('Device_Id', $arrayOfDevId)
                            ->where('Api_Id', $api->Api_Id)
                            ->pluck('Device_Id')
                            ->toArray();
    
            $update = DB::table('devices')
                    ->whereIn('Device_Id', $arrayNotDevId)
                    ->where('Api_Id',$api->Api_Id)
                    ->update(['Status' => 'Removed']);

            if($update){
                LogJob::dispatch('','Devices removed '.$arrayNotDevId ?? 'Unknown'.'');

            }

            $updateTicketRemarks = DB::table('zoho_desks')
                            ->whereIn('Device_Id', $arrayNotDevId)
                            ->where('Status','Open')
                            ->update(['Remarks' => 'Removed Device']);
    
            $Zoho_Credentials = DB::table('zoho_credentials')
                        ->select('code','clientID','clientSecret','access_token','refresh_token','orgID','departmentID','contactID')
                        ->first();
    
            $TicketID = DB::table('zoho_desks')
                ->select('Ticket_Id','Ticket_Number')
                ->whereIn('Device_Id', $arrayNotDevId)
                ->where('Status','Open')
                ->get();
            \Log::Info('Success Refresh Device');
        } else {
            \Log::warning('Refresh Device something is wrong time issue');
        }
    }

    private function qsys($api,$AuthenticUserId){
        \Log::info('Creation of Refresh Device qsys');
        $arrayOfDevId = [];
            $bearerKey = Crypt::decryptString($api->Variable3);
             $url = "https://reflect.qsc.com/api/public/v0/systems";
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerKey,
            ])->withOptions([
                'verify' => false, // Disable SSL certificate verification
            ])->get($url);
        if ($response->successful()) {
                $data = $response->json();
              
            foreach($data as $data){
               $device_rooms = DB::table('device_rooms')
                                ->select('*')
                                ->where('DeviceRoomID',$data['id'])
                                ->first();
                              //  dd($device_rooms);
                    if($device_rooms){
                        //do nothing
                    }else{
                        DB::table('device_rooms')->insert([ //insert to rooms
                            'DeviceRoomID' => $data['id'], 
                            'DeviceRoomName' => $data['name'],
                            'DeviceRoomLocation' => '',
                            'Api_Id'=>$api->Api_Id,
                            'created_at' => now(),
                            'updated_at' => null,
                        ]); 
                        LogJob::dispatch('','New Device Room inserted '.$data['id'] . ' - '.$data['name'] .'' ?? 'Unknown'.'');
                    }
                    $SystemId = $data['id'];
                  
                    $url = "https://reflect.qsc.com/api/public/v0/systems/{$SystemId}/items";
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $bearerKey,
                    ])->withOptions([
                        'verify' => false, // Disable SSL certificate verification
                    ])->get($url);
                    $DeviceData = $response->json();
                    
                    foreach($DeviceData as $DeviceData){
                       $arrayOfDevId[] = $DeviceData['id'];

                        $Exist_Device = DB::table('devices')
                                        ->select('Device_Id')
                                        ->where('Device_Id',$DeviceData['id'])
                                        ->first();
                        $Existing_Room = DB::table('device_rooms')
                                        ->select('DeviceRoomID','DeviceRoomName','DeviceRoomLocation')
                                        ->where('DeviceRoomID',$DeviceData['system']['id'])
                                        ->first();
                        if($Exist_Device){
                            //do nothing
                        }else{
                            $ip = $DeviceData['networkConfig']['interfaces'][0]['ipAddress'] ?? "";
                            $mac = $DeviceData['networkConfig']['interfaces'][0]['macAddress'] ?? "";
                            $Serial = $DeviceData['serialNumber'] ?? "";
                        if(isset($DeviceData['networkConfig'])&& $DeviceData['networkConfig'] !== null){
                                DB::table('devices')->insert([ //insert to rooms
                                'Device_Id' => $DeviceData['id'], 
                                'Device_Name' => $DeviceData['name'],
                                'DeviceRoomID' =>$Existing_Room->DeviceRoomID,
                                'Device_Desc'=>$DeviceData['type'],
                                'Device_Loc' => $Existing_Room->DeviceRoomName,
                                'Room_Type' => $Existing_Room->DeviceRoomLocation,
                                'Manufacturer'=>$DeviceData['manufacturer'],
                                'Serial_Number' => $Serial,
                                'IP_Address' => $ip,
                                'Mac_Address' => $mac,
                                'Status' => $DeviceData['status']['message'],
                                'Api_Id' => $api->Api_Id,
                                'created_at' => now(),
                                'updated_at' => null,
                            ]);  

                            LogJob::dispatch('','New Device inserted '.$DeviceData['id'] . ' - '.$DeviceData['name'].'' ?? 'Unknown'.'');

                            DB::table('device_details')->insert([ //insert to rooms
                                'Device_Id' => $DeviceData['id'], 
                                'Remarks' => $DeviceData['status']['details'],
                                'created_at' => now(),
                                'updated_at' => null,
                            ]);  
                            }else{
                                continue; 
                            }
                        }
                    } 
            }
            $arrayNotDevId = DB::table('devices')
            ->whereNotIn('Device_Id', $arrayOfDevId)
            ->where('Api_Id', $api->Api_Id)
            ->pluck('Device_Id')
            ->toArray();
           
            $update = DB::table('devices')
            ->whereIn('Device_Id', $arrayNotDevId)
            ->where('Api_Id', $api->Api_Id)
            ->update([
                'Status' => 'Removed',
                'updated_at' => now()
            ]);
        
            $updateTicketRemarks = DB::table('zoho_desks')
            ->whereIn('Device_Id', $arrayNotDevId)
            ->where('Status','Open')
            ->update(['Remarks' => 'Removed Device']);

            $Zoho_Credentials = DB::table('zoho_credentials')
                    ->select('code','clientID','clientSecret','access_token','refresh_token','orgID','departmentID','contactID')
                    ->first();

            $TicketID = DB::table('zoho_desks')
            ->select('Ticket_Id','Ticket_Number')
            ->whereIn('Device_Id', $arrayNotDevId)
            ->where('Status','Open')
            ->get();
            // $updateTicketRemarks = DB::table('zoho_desks')
            //                 ->whereIn('Device_Id', $arrayNotDevId)
            //                 ->where('Status','Open')
            //                 ->update(['Remarks' => 'Removed Device']);
        }
    }
}

