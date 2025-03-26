<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\ZohoDesk;
use App\Http\Requests\StoreZohoDeskRequest;
use App\Http\Requests\UpdateZohoDeskRequest;
use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\ZohoCredential;
use App\Models\SystemConfiguration;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Collection;
class CreationOfTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $Zoho_Credentials;
    /**
     * Create a new job instance.
     */
    public function __construct($Zoho_Credentials)
    {
        $this->Zoho_Credentials = $Zoho_Credentials;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $Zoho_Credentials = $this->Zoho_Credentials;
            try{
                
              \Log::info('Creation of token', ['exception' => 'success']);
                $accessTokenRequest = [
                    'form_params' => [
                        'code' => $Zoho_Credentials->code, //Authorization code obtained after generating the grant token.
                        'grant_type' => 'authorization_code',
                        'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
                        'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
                        'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
                    ]
                ];
		$client = new Client();
		$response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
   		'form_params' => $accessTokenRequest['form_params'],
   		 'verify' => false // Disable SSL verification (unsafe for production)
		]);

                //$response = $client->post('https://accounts.zoho.com/oauth/v2/token', $accessTokenRequest);
            
                $accessTokenData = json_decode($response->getBody()->getContents(), true);
                if(isset($accessTokenData['error'])){
                    $accessTokenData = $this->recreateAccessToken($Zoho_Credentials);
                    $Zoho_Credentials = DB::table('zoho_credentials')
                                        ->update(['code'=>$accessTokenData]);
                }else{
                    $Zoho_Credentials = DB::table('zoho_credentials')
                                        ->update(['code'=>$accessTokenData['access_token'],
                                        'refresh_token'=>$accessTokenData['refresh_token']
                                        ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Error Creation of token', ['exception' => $e->getMessage()]);
                throw $e;
            }
    }
    private function recreateAccessToken($Zoho_Credentials){
        try{
            $refreshTokenRequest = [
                'form_params' => [
                    'refresh_token' => $Zoho_Credentials->refresh_token, //Authorization code obtained after generating the grant token.
                    'grant_type' => 'refresh_token',
                    'client_id' => $Zoho_Credentials->clientID, //Client ID obtained after registering the client.
                    'client_secret' => $Zoho_Credentials->clientSecret, //Client secret obtained after registering the client.
                    'scope' => 'Desk.tickets.CREATE,Desk.tickets.READ,Desk.tickets.UPDATE',
                    'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
                ]
            ];
            $client = new Client();
            $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
                'form_params' => $refreshTokenRequest['form_params'],
                'verify' => false
            ]);
            $accessTokenData = json_decode($response->getBody()->getContents(), true);
        // dd($accessTokenData);
            $newAccessToken = $accessTokenData['access_token'];
        // $newRefreshToken = $accessTokenData['refresh_token'];

            $Zoho_Credentials = DB::table('zoho_credentials')
            ->update(['code'=>$newAccessToken]);
            //dd($response,$accessTokenData, $newAccessToken );
            return $newAccessToken;
        } catch (\Throwable $e) {
            \Log::error('Error RecreateToken', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }
}
