<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SystemConfig;
use App\Http\Middleware\superAdminOnly;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Ticket;

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('resetPassword', [HomeController::class, 'resetPassword'])->name('resetPassword');
Route::get('updatePassword', [HomeController::class, 'updatePassword'])->name('updatePassword');

// Route::get('/create', [HomeController::class, 'create'])->name('create');
// Route::post('/register', [HomeController::class, 'register'])->name('register');

Route::middleware('auth')->group(function () {
    // dashboard
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/fetch-config', [SystemConfig::class, 'SystemVal'])->name('SystemVal');
    Route::get('/refresh-device', [HomeController::class, 'refreshDevice'])->name('refresh-device');
    Route::get('/refresh-device-with-param', [HomeController::class, 'refreshDeviceWithParam'])->name('refreshDeviceWithParam');
    Route::get('/device-Info', [HomeController::class, 'deviceInfo'])->name('deviceInfo');
    Route::get('/reports', [HomeController::class, 'InitReports'])->name('InitReports');
    Route::get('/AlertNotification', [HomeController::class, 'AlertNotification'])->name('AlertNotification');
    Route::get('/filter-region', [HomeController::class, 'FilterByRegion'])->name('FilterByRegion');
    Route::get('/sort', [HomeController::class, 'AscDesc'])->name('AscDesc');
    Route::get('/search', [HomeController::class, 'SearchDevice'])->name('SearchDevice');
    Route::get('/get-region', [HomeController::class, 'getRegion'])->name('getRegion');
    Route::get('/get-rooms', [HomeController::class, 'getRooms'])->name('getRooms');
    Route::get('/get-offline-incident', [HomeController::class, 'getOfflineIncident'])->name('get-offline-incident');
    Route::get('/uptime', [HomeController::class, 'InitUptime'])->name('InitUptime');
    // Route::get('/reliablerooms', [HomeController::class, 'ReliableRooms'])->name('ReliableRooms');
    Route::get('/DeleteCompanyAccount', [HomeController::class, 'DeleteCompanyProfile'])->name('DeleteCompanyProfile');
    Route::get('/DeleteApiAccount', [HomeController::class, 'DeleteApiAccount'])->name('DeleteApiAccount');
    Route::get('/reliable-rooms', [HomeController::class, 'reliableRooms'])->name('ReliableRooms');
    Route::get('/edit-company', [HomeController::class, 'editCompanyProfile'])->name('editCompanyProfile');
    Route::get('/update-company', [HomeController::class, 'updateCompanyProfile'])->name('updateCompanyProfile');
    // filter rooms
    Route::get('/rooms', [HomeController::class, 'rooms'])->name('rooms');
    // date notification
    
    Route::get('/date-notif', [TicketController::class, 'dateNotif'])->name('date-notif');

    Route::middleware(superAdminOnly::class)->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])
            ->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);
    });

    // Admin
    Route::middleware(superAdminOnly::class)->prefix('admin')->group(function () {
        // System Configuration
        Route::get('/user-account', [AdminController::class, 'userAccount'])->name('user-account');
        Route::get('/user-access', [AdminController::class, 'userAccess'])->name('user-access');
        Route::get('/api-accounts', [AdminController::class, 'apiAccounts'])->name('api-accounts');
        Route::get('/api-access', [AdminController::class, 'ApiAccess'])->name('ApiAccess');
        Route::post('/add-api', [AdminController::class, 'addApi'])->name('add-api');
        Route::get('/company-profiles', [AdminController::class, 'companyProfiles'])->name('company-profiles');
        Route::post('/add-profile', [AdminController::class, 'addProfile'])->name('add-profile');
        Route::get('/systemConfig', [AdminController::class, 'systemConfig'])->name('systemConfig');
        Route::get('/api-access', [AdminController::class, 'ApiAccess'])->name('ApiAccess');
        Route::get('/add-api-access', [AdminController::class, 'AddApiAccess'])->name('AddApiAccess');
        Route::get('/fetch-api-access', [AdminController::class, 'FetchApiAccess'])->name('FetchApiAccess');
        Route::get('/UserAccess', [AdminController::class, 'InitUserAccess'])->name('InitUserAccess');
        Route::get('/add-user-access', [AdminController::class, 'SaveUserAccess'])->name('SaveUserAccess');

        // ticketing
        Route::get('/create-device-ticket', [TicketController::class, 'createDeviceTticket'])->name('createDeviceTticket');
        Route::get('/all-ticket', [TicketController::class, 'allTicket'])->name('all-ticket');

        // user
        Route::get('/delete-user', [AdminController::class, 'deleteUser'])->name('delete-user');

        // SystemConfig
        Route::post('/add-config', [AdminController::class, 'addConfig'])->name('addConfig');
        Route::get('/edit-config', [SystemConfig::class, 'editConfig'])->name('edit-config');
        Route::post('/update-config', [SystemConfig::class, 'updateConfig'])->name('update-config');


    });
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';