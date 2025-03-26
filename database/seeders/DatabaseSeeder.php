<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\ZohoCredential;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'First_Name' => 'Kael',
                'Last_Name' => 'Lazaro',
                'Position' => 'programmer',
                'Status' => 'active',
                'Start_Date' => '04/23/2024',
                'email' => 'admin@gmail.com',
                'password' => 'password',
                'usertype' => 2,
            ],
            [
                'First_Name' => 'User1',
                'Last_Name' => 'User',
                'Position' => 'programmer',
                'Status' => 'active',
                'Start_Date' => '04/23/2024',
                'email' => 'user1@gmail.com',
                'password' => 'password',
            ],
            [
                'First_Name' => 'User2',
                'Last_Name' => 'User',
                'Position' => 'programmer',
                'Status' => 'active',
                'Start_Date' => '04/23/2024',
                'email' => 'user2@gmail.com',
                'password' => 'password',
            ],
        ];
        $configData = [
            [
                "key" => "Zoho_Code",
                "value" => "1000.9c715d291b538d4dba1f05afe75d1eba.bfb5f6993e51...",
                "description" => "Creation of Access Tokens",
            ],
            [
                "key" => "Zoho_ClientId",
                "value" => "1000.JGIUEX7WXCD3VZMQQNE4ZOW3VYW1CG",
                "description" => "Header for Zoho Client Id",
            ],
            [
                "key" => "Zoho_ClientSecret",
                "value" => "31398e831566ffa582da422a1379cd8590599a788d",
                "description" => "Header for Zoho client secret",
            ],
            [
                "key" => "Zoho_OrgId",
                "value" => "680708905",
                "description" => "Code for Zoho org Id",
            ],
            [
                "key" => "Zoho_DepartmentId",
                "value" => "351081000001812222",
                "description" => "Code for Department Id",
            ],
            [
                "key" => "Zoho_ContactId",
                "value" => "351081000124198280",
                "description" => "Code for Contact Id",
            ],
            [
                "key" => "Device_RefreshTime",
                "value" => "10",
                "description" => "This is the value of the refresh time",
            ],
            [
                "key" => "Zoho_CreateTicketTimer",
                "value" => "10",
                "description" => "This is the timer for the creation of tickets",
            ],
            [
                "key" => "Crestron_ApiRefresh",
                "value" => "2",
                "description" => "Api Refresh Value",
            ],
        ];
        foreach ($users as $user) {
            User::factory()->create($user);
        }

        foreach ($configData as $data) {
            $config = new SystemConfiguration();
            $config->Code_Name = $data['key'];
            $config->Code_Value = $data['value'];
            $config->Code_Description = $data['description'];
            $config->save();
        }

        $code = SystemConfiguration::where('Code_Name', 'Zoho_Code')->first();
        $clientId = SystemConfiguration::where('Code_Name', 'Zoho_ClientId')->first();
        $clientSecret = SystemConfiguration::where('Code_Name', 'Zoho_ClientSecret')->first();
        $orgID = SystemConfiguration::where('Code_Name', 'Zoho_OrgId')->first();
        $departmentID = SystemConfiguration::where('Code_Name', 'Zoho_DepartmentId')->first(); // Fix typo here
        $contactID = SystemConfiguration::where('Code_Name', 'Zoho_ContactId')->first();

        $creds = new ZohoCredential();
        $creds->code = $code['Code_Value'];
        $creds->clientID = $clientId['Code_Value'];
        $creds->clientSecret = $clientSecret['Code_Value'];
        $creds->orgID = $orgID['Code_Value'];
        $creds->departmentID = $departmentID['Code_Value'];
        $creds->contactID = $contactID['Code_Value'];
        $creds->save();
    }
}
