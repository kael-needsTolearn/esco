<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\CompanyProfileDetails;
use App\Models\Device;
use App\Models\ZohoCredential;
use App\Models\ZohoDesk;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    private $credential;
    private $code;
    private $clientId;
    private $clientSecret;
    private $orgID;

    public function __construct()
    {
        try{
        $this->credential = ZohoCredential::first();
        if ($this->credential) {
            $this->code = $this->credential->code;
            $this->clientId = $this->credential->clientID;
            $this->clientSecret = $this->credential->clientSecret;
            $this->orgID = $this->credential->orgID;
        }
    } catch (\Throwable $e) {
        dd($e->getMessage());
        return response()->json([
            'error' => 'function editConfig - '.$e->getMessage()
        ]);
    }
    }
    public function createDeviceTticket()
    {
        try{
        $user = Auth::user();
        // get all offline devices
        $off_devices = Device::where('Status', 'Offline')->get();
        // get all online devices
        $on_devices = Device::where('Status', 'Online')->get();

        if ($user->usertype != 0) {

            // if there is/are offline device/devices
            if ($off_devices) {
                foreach ($off_devices as $device) {

                    $deviceID = $device->Mac_Address . "_-1";
                    $deviceDesc = $device->Device_Desc;
                    $manufacturer = $device->Manufacturer;
                    $deviceName = $device->Device_Name;
                    $location = $device->Device_Loc;
                    $room = $device->Room_Type;
                    $cpd = CompanyProfileDetails::where('Api_Id', $device->Api_Id)->first();
                    $cp = CompanyProfile::where('Company_Id', $cpd->Company_Id)->first();
                    $companyName = $cp->Company_Name;
                    $companyID = $cp->Company_Id;
                    $updatedTime = $device->updated_at;
                    // check if there is an open/active ticket
                    $activeTicket = ZohoDesk::where('Device_Id', $deviceID)->where('Status', 'Open')->first();
                    if (!$activeTicket) {
                        // create ticket if the device is offline and there is no currentlt open ticket
                        $this->createTicket($companyID, $deviceID, $deviceDesc, $companyName, $manufacturer, $deviceName, $location, $room, $updatedTime);
                    }
                }
                //dd("done creating ticket i assume");
            }
            // if there is/are online device/devices
            if ($on_devices) {
                foreach ($on_devices as $device) {
                    $deviceID = $device->Mac_Address . "_-1";
                    // check if there is an open/active ticket
                    $activeTicket = ZohoDesk::where('Device_Id', $deviceID)->where('Status', 'Open')->first();
                    if ($activeTicket) {
                        $accessToken = $this->createToken($this->clientId, $this->clientSecret, $this->code);
                        if ($accessToken == null) {
                            // return redirect('/api/ticket-web');
                            $refresh_token = Cache::get('refresh_token');
                            $this->createNewToken($refresh_token, $this->clientId, $this->clientSecret);
                        }
                        $comment =  $this->commentCount($accessToken, $activeTicket->Ticket_Id);
                        $count = count($comment->data);
                        $ticketInfo = $this->getTicketInfo($accessToken, $activeTicket->Ticket_Id);
                        $ticketStatus = $ticketInfo->statusType;

                        $timestamp = $ticketInfo->modifiedTime;
                        // Parse the timestamp using Carbon
                        $date = Carbon::parse($timestamp);
                        $date->setTimezone('Asia/Manila');
                        $ticketCreatedDate = $date->format('F j, Y g:i a');
                        $now = Carbon::now();
                        $daysDifference = $date->diffInMinutes($now);
                        // check if there is no comment, the timeof ticket is already one day(1440 minutes) and the status is open
                        if ($count < 1 && $daysDifference >= 1440 && $ticketStatus == "Open") {
                            $ticketData = [
                                "isPublic" => "false",
                                "attachmentIds" => ["123456"],
                                "contentType" => "html",
                                "content" => "For Site Dispatch"
                            ];
                            // $this->updateTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                            $this->commentTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        }
                        if ($ticketStatus !== "Open") {
                            $activeTicket->Status = $ticketStatus;
                            $activeTicket->save();
                        }
                        // else {
                        //     $data = $comment->data;

                        //     // Sort comments by commentedTime in descending order
                        //     usort($data, function ($a, $b) {
                        //         return strtotime($b->commentedTime) - strtotime($a->commentedTime);
                        //     });

                        //     // Get the 3 latest comments
                        //     $latestComments = array_slice($data, 0, 3);

                        //     $isSiteDispatch = false;
                        //     foreach ($data as $datum) {
                        //         if ($datum->content === "For site dispatch") {
                        //             $isSiteDispatch = true;
                        //             break;  // No need to check further if we've already found the comment
                        //         }
                        //     }
                        //     // Check if 3 latest are all offline
                        //     $allOffline = true;
                        //     foreach ($latestComments as $latestComment) {
                        //         if ($latestComment->content !== "Device is Offline") {
                        //             $allOffline = false; // Set to false if any comment is not "Device is Offline"
                        //             break;  // No need to check further if we find a comment that is not "Device is Offline"
                        //         }
                        //     }
                        //     // dd($allOffline);


                        //     $allOnline = true; // Assume all devices are online by default
                        //     foreach ($latestComments as $latestComment) {
                        //         if ($latestComment->content !== "Device is Online") {
                        //             $allOnline = false; // Set to false if any comment is not "Device is Online"
                        //             break;  // No need to check further if we find a comment that is not "Device is Online"
                        //         }
                        //     }
                        //     // dd($allOnline);

                        //     if (!$isSiteDispatch && $allOffline) {
                        //         $ticketData = [
                        //             "isPublic" => false,
                        //             "attachmentIds" => ["123456"],
                        //             "contentType" => "html",
                        //             "content" => "For site dispatch"
                        //         ];
                        //         // $this->updateTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        //         $this->commentTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        //     } elseif (!$isSiteDispatch && $allOnline) {
                        //         $ticketData = [
                        //             "statusType" => "Closed",
                        //             "status" => "Closed",
                        //             "cf" => [
                        //                 "cf_status_type" => "Closed"
                        //             ]
                        //         ];
                        //         $this->updateTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        //         $activeTicket->Status = "Closed";
                        //         $activeTicket->save();
                        //     } else {
                        //         $ticketData = [
                        //             "isPublic" => false,
                        //             "attachmentIds" => ["123456"],
                        //             "contentType" => "html",
                        //             "content" => "Device is " . $device->Status
                        //         ];
                        //         // $this->updateTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        //         $this->commentTicket($activeTicket->Ticket_Id, $accessToken, $ticketData);
                        //     }
                        // }
                    }
                    // if($activeTicket){

                    // }
                }
            }
        }

        return;
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function editConfig - '.$e->getMessage()
            ]);
        }
    }
    public function createTicket($companyID, $deviceID, $deviceDesc, $companyName, $manufacturer, $deviceName, $location, $room, $updatedTime)
    {
        $client = new Client();
        // ticket create
        try {
            $accessToken = $this->createToken($this->clientId, $this->clientSecret, $this->code);
            if ($accessToken == null) {
                // return redirect('/api/ticket-web');
                // $refresh_token = Cache::get('refresh_token');
                $refresh_token = $this->credential->refresh_token;
                $this->createNewToken($refresh_token, $this->clientId, $this->clientSecret);
            }
            // dd( "Acess Token : " . $accessToken);
            $now = Carbon::now();
            $format_updatedTime = Carbon::parse($updatedTime);
            $difference = $now->diff($format_updatedTime);
            $diffDays = $difference->days;
            $diffHours = $difference->h;
            $diffMinutes = $difference->i;

            $diffAll = $difference->format('%d days, %h hours, %i minutes ago');


            $dateNow = Carbon::now()->format('F d, Y g:ia');
            $dateLast = $updatedTime->format('F d, Y g:ia');

            // Ticket data
            $ticketData = [
                // "ticketNumber" => null, // you can assign a value here if needed
                'subject' => $deviceDesc . " " . $deviceID . " went Offline.", //Subject of the ticket
                'description' =>
                "Company Name: $companyName<br><br>" .
                    "Description:<br>" .
                    "&nbsp;&nbsp;&nbsp;&nbsp;Device: $manufacturer <br>" .
                    "&nbsp;&nbsp;&nbsp;&nbsp;Device Name: $deviceName<br>" .
                    "&nbsp;&nbsp;&nbsp;&nbsp;Device Type: $deviceDesc<br>" .
                    "&nbsp;&nbsp;&nbsp;&nbsp;Event Message: \" Device Offline\"<br><br>" .
                    // "Location: $location $room<br>" .
                    "Created Time: $dateNow <br> " .
                    "Logged last Online: $dateLast <br> " .
                    "Elapse Time: $diffAll ",
                'statusType' => 'Open',
                'assigneeId' => null,
                'departmentId' => $this->credential->departmentID, // ID of the department to which the ticket belongs
                'contactId' => $this->credential->contactID, // Replace with the ID of the contact associated with the ticket / contact who raised the ticket.
                'priority' => null,
                'classification' => "Incident",
                'category' => "Audio Problem",
                // 'email' => 'sample@example.com', // Replace with the email of the contact associated with the ticket
                'cf' => [
                    // 'cf_requestor' => 'Test Requestor', // you can assign a value here if needed
                    'cf_account_manager' => 'Rhica Mae Alvarez', // you can assign a value here if needed
                    'cf_billing_type' => "Billable", // you can assign a value here if needed
                    'cf_sor_1' => 'TBA', // SOR
                    'cf_region' => 'Philippines', // you can assign a value here if needed
                    'cf_country_1' => "Philippines", // you can assign a value here if needed
                    'cf_sub_category' => "Codec", // you can assign a value here if needed
                    'cf_contract_type' => "ADHOC", // you can assign a value here if needed
                    "cf_floor_1" => $location,
                    "cf_status_type" => "open",
                    "cf_location_1" => $room,
                ],
                'customFields' => [
                    "Contract Type" => "ADHOC",
                    "Account Manager" => "Rhica Mae Alvarez",
                    "SOR" => "TBA",
                    "Billing Type" => "Billable",
                    "Room Name/Room Number" => "Null",
                    "Site Address" => "Philippines",
                    "Region" => "Philippines",
                    "Floor" => $location,
                ]
            ];
            // Send POST request to create ticket
            $response = $client->post('https://desk.zoho.com/api/v1/tickets', [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ],
                'json' => $ticketData,
            ]);
            // dd($response->getBody()->getContents());
            // Handle response
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
            // $responseBody = $response->getBody();
            // dd($statusCode, $responseBody);
            if ($statusCode == 200) {
                echo "Ticket created successfully.\n";
                $ticketId = $responseBody->id; // Contains information about the created ticket
                $ticketNumber = $responseBody->ticketNumber; // Contains information about the created ticket
                $status = $responseBody->statusType;
                // $companyID = $companyID;
                $remarks = "Walang remarks";
                $subject = $responseBody->subject;
                // dd($ticketId, $status, $subject);
                $this->createZohoDesk($ticketId, $ticketNumber, $companyID, $deviceID, $status, $remarks, $subject, $dateLast, $diffAll);
            } else {
                echo "Failed to create ticket. HTTP code: $statusCode\n";
                // echo "Error message: $responseBody\n"; // Print error message from the response
                $this->createTicket($companyID, $deviceID, $deviceDesc, $companyName, $manufacturer, $deviceName, $location, $room, $updatedTime);
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function createTicket - '.$e->getMessage()
            ]);
            $message = $response_data->getContent();
            // dd($message);
        }
    }
    public function getTicketInfo($accessToken, $ticketID)
    {
        $client = new Client();
        try {
            $response = $client->get("https://desk.zoho.com/api/v1/tickets/{$ticketID}", [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ]
            ]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
            return $responseBody;
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function getTicketInfo - '.$e->getMessage()
            ]);
        }
    }
    public function updateTicket($ticketID, $accessToken, $ticketData)
    {
        $client = new Client();
        try {
            $response = $client->patch("https://desk.zoho.com/api/v1/tickets/{$ticketID}", [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ],
                'json' => $ticketData,
            ]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
            // dd($responseBody);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function updateTicket - '.$e->getMessage()
            ]);
        }
    }
    public function allTicket()
    {
        $client = new Client();
        $accessToken = $this->createToken($this->clientId, $this->clientSecret, $this->code);
        if ($accessToken == null) {
            // return redirect('/api/ticket-web');
            $refresh_token = Cache::get('refresh_token');
            $this->createNewToken($refresh_token, $this->clientId, $this->clientSecret);
        }
        try {
            $response = $client->get("https://desk.zoho.com/api/v1/tickets/351081000122633300?include=contacts,products,assignee,departments,team", [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ],
            ]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
        }catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function allTicket - '.$e->getMessage()
            ]);
        }
    }
    public function commentTicket($ticketID, $accessToken, $ticketData)
    {
        $client = new Client();
        try {
            $response = $client->post("https://desk.zoho.com/api/v1/tickets/{$ticketID}/comments", [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ],
                'json' => $ticketData,
            ]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
            return;
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function commentTicket - '.$e->getMessage()
            ]);
        }
    }
    public function commentCount($accessToken, $ticketID)
    {
        $client = new Client();
        try {
            $response = $client->get("https://desk.zoho.com/api/v1/tickets/{$ticketID}/comments", [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'orgId' => $this->orgID // Add orgId to the headers
                ],
            ]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents());
            return $responseBody;
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function commentCounts - '.$e->getMessage()
            ]);
        }
    }
    // generate new token
    public function createToken($clientId, $clientSecret, $code)
    {
        // Get access token using Authorization Code Grant
try{
        // Self Client
        $accessTokenRequest = [
            'form_params' => [
                'code' => $code, //Authorization code obtained after generating the grant token.
                'grant_type' => 'authorization_code',
                'client_id' => $clientId, //Client ID obtained after registering the client.
                'client_secret' => $clientSecret, //Client secret obtained after registering the client.
                'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
            ]
        ];
        // dd($accessTokenRequest);
        $client = new Client();
        $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
            'form_params' => $accessTokenRequest['form_params'],
            'verify' => false
        ]);

        $accessTokenData = json_decode($response->getBody()->getContents(), true);
        // dd($accessTokenData);
        if (isset($accessTokenData['error'])) {
            $refresh_token = $this->credential->refresh_token;
            $access_token = $this->createNewToken($refresh_token, $clientId, $clientSecret);
            // dd($access_token);
            return $access_token;
        } else {
            if (isset($accessTokenData['access_token']) && isset($accessTokenData['refresh_token'])) {
                $access_token = $accessTokenData['access_token'];
                $refresh_token = $accessTokenData['refresh_token'];
                $this->credential->access_token = $accessTokenData['access_token'];
                $this->credential->refresh_token = $accessTokenData['refresh_token'];
                $this->credential->save();
                // Cache::put('refresh_token', $refresh_token, now()->addDays(30)); // Adjust the expiration time as needed
                return $access_token;
            } else {
                // Handle the case when access_token or refresh_token is missing
                // Return an error or throw an exception as needed
                return $access_token = null;
            }
        }
    } catch (\Throwable $e) {
        dd($e->getMessage());
        return response()->json([
            'error' => 'function createToken - '.$e->getMessage()
        ]);
    }
    }
    // refresh Token
    public function createNewToken($refresh_token, $clientId, $clientSecret) //$code is the refresh token
    {
        // Get access token using Authorization Code Grant
        $refreshTokenRequest = [
            'form_params' => [
                'refresh_token' => $refresh_token, //Authorization code obtained after generating the grant token.
                'grant_type' => 'refresh_token',
                'client_id' => $clientId, //Client ID obtained after registering the client.
                'client_secret' => $clientSecret, //Client secret obtained after registering the client.
                'scope' => 'Desk.tickets.CREATE,Desk.tickets.READ,Desk.tickets.UPDATE,Desk.tickets.ALL',
                'redirect_uri' => env('APP_URL') . '/admin/create-device-ticket' //Redirect URI mentioned while registering the client.
            ]
        ];
        $client = new Client();
        try {
            $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
                'form_params' => $refreshTokenRequest['form_params'],
                'verify' => false
            ]);
            // dd($response);
            $accessTokenData = json_decode($response->getBody()->getContents(), true);
            // dd($accessTokenData);
            if (isset($accessTokenData['error'])) {
                $refresh_token = null;
                $this->credential->refresh_token = $refresh_token;
                // Cache::put('refresh_token', $refresh_token, now()->addDays(30));
                return null;
            } else {
                $accessToken = $accessTokenData['access_token'];
                // dd($accessToken);
                return $accessToken;
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function createNewToken - '.$e->getMessage()
            ]);
        }
    }

    public function createZohoDesk($ticketId, $ticketNumber, $companyId, $deviceId, $status, $remarks, $subject, $Log_Last_Online, $Elapse_Time)
    {
        // Validate input parameters if necessary
        try{
        $zohoDesk = new ZohoDesk();
        $zohoDesk->Ticket_Id = $ticketId;
        $zohoDesk->Ticket_Number = $ticketNumber;
        $zohoDesk->Company_Id = $companyId;
        $zohoDesk->Device_Id = $deviceId;
        $zohoDesk->Status = $status;
        $zohoDesk->Remarks = $remarks;
        $zohoDesk->Log_Last_Online = $Log_Last_Online;
        $zohoDesk->Elapse_Time = $Elapse_Time;
        $zohoDesk->Subject = $subject;
        $zohoDesk->save();
    } catch (\Throwable $e) {
        dd($e->getMessage());
        return response()->json([
            'error' => 'function createZohoDesk - '.$e->getMessage()
        ]);
    }
        // try {
        //     DB::insert(
        //         'INSERT INTO zoho_desks (Ticket_Id, Ticket_Number, Company_Id, Device_Id, Status, Remarks, Log_Last_Online, Elapse_Time, Subject) 
        //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        //         [$ticketId, $ticketNumber, $companyId, $deviceId, $status, $remarks, $Log_Last_Online, $Elapse_Time, $subject]
        //     );

        //     // $zohoDesk->save();
        //     // Optionally return the saved ZohoDesk entity or its ID
        // } catch (\Exception $e) {
        //     dd($e);
        //     // Handle the error, log it, or throw a custom exception
        //     // Example: throw new CustomException("Failed to save ZohoDesk: " . $e->getMessage());
        // }
    }

    public function dateNotif(Request $request)
    {
        try{
        $daterange = explode(' - ', $request->date);
        $start_date = Carbon::createFromFormat('m/d/Y', $daterange[0])->startOfDay();
        $end_date = Carbon::createFromFormat('m/d/Y', $daterange[1])->endOfDay();

        // Define the time range for the last 24 hours
        $later = Carbon::now()->subDay();
        $now = Carbon::now();

        // Count of new tickets created within the last 24 hours
        $new = DB::table('zoho_desks')
            ->select(DB::raw('count(*) as Ticket_New'))
            ->where('Status', '=', 'Open')
            ->whereDate(DB::raw('DATE(created_at)'), '=', Carbon::today())
            ->get();

        // Count of resolved tickets created within the specified date range that are 1 day old or older
        $res = ZohoDesk::where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)
            ->where('Status', 'Closed')
            ->orWhere('Status', 'Resolved by Agent Onsite')
            ->where('created_at', '<=', Carbon::now()->subDay())
            ->count();


        $unres = DB::table('zoho_desks')
            ->select(DB::raw('count(*) as Ticket_Count'))
            ->where('Status', '=', 'Open')
            ->get();

        // ZohoDesk::where('created_at', '>=', $start_date)
        //     ->where('created_at', '<=', $end_date)
        //     ->where('Status', 'Open')
        //     // ->orWhere('Status', 'Resolved by Agent Onsite')
        //     ->where('created_at', '<=', Carbon::now()->subDay()) // Make sure the ticket is at least one day old
        //     ->count();


        return response()->json([
            'new' => $new,
            'res' => $res,
            'unres' => $unres,
        ]);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return response()->json([
                'error' => 'function createTicket - '.$e->getMessage()
            ]);
        }
    }
}
