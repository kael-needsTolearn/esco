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
use Carbon\Carbon;
class uhooUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $DataAccess;
    private $accessToken;
    /**
     * Create a new job instance.
     */
    public function __construct($DataAccess,$accessToken)
    {
        $this->DataAccess = $DataAccess;
        $this->accessToken = $accessToken;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
        $DataAccess = $this->DataAccess;
        $accessToken = $this->accessToken;

        $combined = array_combine($DataAccess['MacAddress'], $DataAccess['SerialNumber']);
        foreach($combined as $Mac => $Serial){
            $ctr = 0;
            $url = 'https://api.uhooinc.com/v1/devicedata';
            $data = [
                        'macAddress' => $Mac, //$macAddress
                        'mode' => 'minute' // Data mode
                    ];
            $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken
                    ])->asForm()->post($url, $data);

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
                            $value < 15 => 'Bad',
                            $value <= 20 && $value >= 16 => 'Moderate',
                            $value >= 21 && $value <= 26 => 'Good',
                            $value >= 27 && $value <= 100 => 'Moderate',
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
        \Log::info('Updating Device', [
            'uhoo Update Success' => 'success'
        ]);
        }catch(\Throwable $e){
            \Log::error('Updating Device', [
                'uhoo Update Error' => $e->getMessage()
            ]);
        }
    }

    //         } else {
    //            // \Log::error('uhooAccessCode', ['exception' => $e->getMessage()]);            }
    //     } else {
    //        // \Log::error('uhooAccessCode', ['exception' => $e->getMessage()]);        }
    // }
}
