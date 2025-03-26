<?php

namespace App\Jobs;

use App\Models\ApiAccount;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\User;
use App\Models\UserAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class RefreshDeviceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $accesses = UserAccess::where('User_Id', $user->id)->get();

            if ($accesses->isNotEmpty()) {
                foreach ($accesses as $access) {
                    $company = CompanyProfile::where('Company_Id', $access->Company_Id)->first();

                    if (!$company) {
                        continue; // Skip if company not found
                    }

                    $cpds = CompanyProfileDetails::where('Company_Id', $company->Company_Id)->get();

                    foreach ($cpds as $cpd) {
                        $api = ApiAccount::where('Api_Id', $cpd->Api_Id)->first();

                        if (!$api || $api->Platform !== 'xio') {
                            continue; // Skip if API not found or platform not 'xio'
                        }

                        $subscriptionKey = Crypt::decryptString($api->Variable1);
                        $accountId = Crypt::decryptString($api->Variable2);
                        $url = "https://api.crestron.io/api/v1/device/accountid/{$accountId}/devices";

                        try {
                            $response = Http::withHeaders([
                                'XiO-subscription-key' => $subscriptionKey,
                            ])->get($url);

                            if ($response->successful()) {
                                $data = $response->json();

                                foreach ($data as $item) {
                                    $existingDevice = Device::where('Device_Id', $item['device-cid'])->first();

                                    if ($existingDevice) {
                                        // Update existing device attributes if necessary
                                        $existingDevice->fill([
                                            'Device_Loc' => '',
                                            'Room_Type' => '',
                                            'Status' => $item['device-status'],
                                        ]);
                                        $existingDevice->save();
                                    } else {
                                        // Create new device if not exists
                                        $device = new Device([
                                            'Device_Id' => $item['device-cid'],
                                            'Device_Name' => $item['device-name'],
                                            'Device_Desc' => $item['device-category'],
                                            'Device_Loc' => '',
                                            'Room_Type' => '',
                                            'Manufacturer' => $item['device-manufacturer'],
                                            'Serial_Number' => $item['serial-number'],
                                            'Mac_Address' => substr($item['device-cid'], 0, -3),
                                            'Status' => $item['device-status'],
                                            'Company_Id' => $company->Company_Id,
                                        ]);
                                        $device->save();
                                    }
                                }
                            } else {
                                return; // Stop processing if API request fails
                            }
                        } catch (\Exception $e) {
                            // Log or handle exception
                            continue;
                        }
                    }
                }
            }
        }
    }
}
