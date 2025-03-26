<?php

use App\Mail\WelcomeMail;
use App\Events\TestingEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\AuthenticateSession;

use App\Http\Controllers\SystemConfig;
use App\Http\Middleware\superAdminOnly;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use PHPUnit\Framework\Attributes\Ticket;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ZohoDeskController;
use App\Http\Middleware\PreventMultipleLogin;
use App\Http\Controllers\HomeControllerUpdate;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('resetPassword{token}', [HomeController::class, 'resetPassword'])->name('resetPassword');
Route::get('/updatePassword', [HomeController::class, 'sendNotification'])->name('sendNotification');//email notification
Route::get('/EmailMessage', [HomeController::class, 'EmailMessage'])->name('EmailMessage');//notification
Route::post('/SaveResetPassword', [HomeController::class, 'SaveResetPassword'])->name('SaveResetPassword');
Route::post('/logout',[AuthenticatedSessionController::class,'destroy'])->name('destroy');

Route::post('/authenticate', [AdminController::class, 'authenticate'])->name('authenticate');
Route::post('/sessionTimeoutSave', [HomeController::class, 'sessionTimeoutSave'])->name('sessionTimeoutSave');
Route::post('/pwvalidation', [HomeController::class, 'pwvalidation'])->name('pwvalidation');

Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(HomeController::class)->group(function(){
    	
        Route::get('/refresh-device-with-param','refreshDeviceWithParam')->name('refreshDeviceWithParam');
        Route::get('/device-Info',  'deviceInfo')->name('deviceInfo');
        Route::get('/reports', 'InitReports')->name('InitReports')->middleware('App\Http\Middleware\NoCacheMiddleware');
        Route::get('/uhooAccessCode', 'uhooAccessCode')->name('uhooAccessCode');
        Route::get('/uhooCreateToken', 'uhooCreateToken')->name('uhooCreateToken');
        Route::get('/uhooDeviceList', 'uhooDeviceList')->name('uhooDeviceList');
        Route::get('/uhooDashboard', 'uhooDashboard')->name('uhooDashboard');
        Route::get('/uhoo-display', 'uhooDisplay')->name('uhooDisplay');
        Route::get('/uhoo_all_device', 'uhoo_all_device')->name('uhoo_all_device');
        Route::get('/uhoo_sort', 'uhoo_sort')->name('uhoo_sort');
        Route::get('/uhooDeviceData', 'uhooDeviceData')->name('uhooDeviceData');
        Route::get('/uhoo_create_history', 'uhoo_create_history')->name('uhoo_create_history');
        Route::get('/uhooUpdater', 'uhooUpdater')->name('uhooUpdater');
        Route::get('/uhoo_days_in_month', 'uhoo_days_in_month')->name('uhoo_days_in_month');
        Route::get('/uhoo_update_val', 'uhoo_update_val')->name('uhoo_update_val');
        Route::get('/uhooTicketCreation', 'uhooTicketCreation')->name('uhooTicketCreation');
        Route::get('/AssetManager', 'AssetManager')->name('AssetManager');
        Route::get('/AlertNotification', 'AlertNotification')->name('AlertNotification');
        Route::get('/ChangeTicketStatus',  'ChangeTicketStatus')->name('ChangeTicketStatus');
        Route::get('/ReportsChangeDate',  'ReportsChangeDate')->name('ReportsChangeDate');
        Route::get('/filter-region', 'FilterByRegion')->name('FilterByRegion');
        Route::get('/sessionTimeout', 'sessionTimeout')->name('sessionTimeout');
        Route::get('/sessionTimeoutChange', 'sessionTimeoutChange')->name('sessionTimeoutChange');
        Route::get('/UpdateField', 'UpdateField')->name('UpdateField');
        Route::get('/filter-Organization', 'FilterByOrganization')->name('FilterByOrganization');
        Route::get('/sort', 'AscDesc')->name('AscDesc');
        Route::get('/search',  'SearchDevice')->name('SearchDevice');
        Route::get('/refresh-device','refreshDevice')->name('refresh-device');
        Route::get('/refresh-notification','refreshNotification')->name('refresh-notification');
        Route::get('/filter-notification','filterNotification')->name('filter-notification');
        Route::get('/refreshDeviceQsys','refreshDeviceQsys')->name('refreshDeviceQsys');
        Route::get('/initdisplay',  'InitDisplay')->name('InitDisplay');
        Route::get('/version2', 'version2')->name('version2');
        Route::get('/dashboard', 'dashboard')->name('dashboard')->middleware('App\Http\Middleware\NoCacheMiddleware');
        Route::get('/refreshDashboard', 'refreshDashboard')->name('refreshDashboard');
        Route::get('/dashboardRefresh', 'dashboardRefresh')->name('dashboardRefresh');
        Route::get('/get-region', 'getRegion')->name('getRegion');
        Route::get('/get-rooms', 'getRooms')->name('getRooms');
        Route::get('/DeviceStatus', 'DeviceStatus')->name('DeviceStatus');
        Route::get('/get-offline-incident',  'getOfflineIncident')->name('get-offline-incident');
        Route::get('/uptime', 'InitUptime')->name('InitUptime');
        // Route::get('/reliablerooms', [HomeController::class, 'ReliableRooms'])->name('ReliableRooms');
        Route::get('/DeleteCompanyAccount', 'DeleteCompanyProfile')->name('DeleteCompanyProfile');
        Route::get('/DeleteApiAccount', 'DeleteApiAccount')->name('DeleteApiAccount');
        Route::get('/reliable-rooms', 'reliableRooms')->name('ReliableRooms');
        Route::get('/edit-company',  'editCompanyProfile')->name('editCompanyProfile');
        Route::get('/update-company',  'updateCompanyProfile')->name('updateCompanyProfile');
        Route::get('/rooms', 'rooms')->name('rooms');
	 
    });
    Route::get('/LoginAuth', [AdminController::class, 'LoginAuth'])->name('LoginAuth')->middleware(PreventMultipleLogin::class);

    Route::get('/LoginProceed{token}', [AdminController::class, 'LoginProceed'])->name('LoginProceed');
    Route::get('/fetch-config', [SystemConfig::class, 'SystemVal'])->name('SystemVal');
    Route::get('/fetch-config-logo', [SystemConfig::class, 'fetchconfiglogo'])->name('fetchconfiglogo');
    Route::get('/fetch-config-user', [SystemConfig::class, 'fetchconfiguser'])->name('fetchconfiguser');
    Route::get('/CompanyAccounts', [SystemConfig::class, 'CompanyAccounts'])->name('CompanyAccounts');
    Route::get('/SystemConfigurationsearch', [SystemConfig::class, 'SystemConfigurationsearch'])->name('SystemConfigurationsearch');
    Route::get('/CreationOfTickets', [ZohoDeskController::class, 'CreationOfTickets'])->name('CreationOfTickets');
    Route::get('/CommentDeviceRemoved', [ZohoDeskController::class, 'CommentDeviceRemoved'])->name('CommentDeviceRemoved');
    Route::get('/CommentInTickets', [ZohoDeskController::class, 'CommentInTickets'])->name('CommentInTickets');
    Route::get('/RetrieveTickets', [ZohoDeskController::class, 'RetrieveTickets'])->name('RetrieveTickets');
    Route::get('/UpdateTickets', [ZohoDeskController::class, 'UpdateTickets'])->name('UpdateTickets');
    Route::get('/date-notif', [TicketController::class, 'dateNotif'])->name('date-notif');

    Route::middleware(superAdminOnly::class)->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])
            ->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);
    });
    // Admin
    Route::middleware(superAdminOnly::class)->prefix('admin')->group(function () {
        Route::controller(AdminController::class)->group(function(){
            Route::get('/user-account', 'userAccount')->name('user-account');
            Route::get('/user-access','userAccess')->name('user-access');
            Route::get('/api-accounts', 'apiAccounts')->name('api-accounts');
            Route::get('/api-access', 'ApiAccess')->name('ApiAccess');
            Route::post('/add-api',  'addApi')->name('add-api');
            Route::get('/company-profiles', 'companyProfiles')->name('company-profiles');
            Route::post('/add-profile', 'addProfile')->name('add-profile');
            Route::get('/systemConfig',  'systemConfig')->name('systemConfig');
            Route::get('/api-access', 'ApiAccess')->name('ApiAccess');
            Route::get('/add-api-access',  'AddApiAccess')->name('AddApiAccess');
            Route::get('/fetch-api-access',  'FetchApiAccess')->name('FetchApiAccess');
            Route::get('/UserAccess', 'InitUserAccess')->name('InitUserAccess');
            Route::get('/add-user-access',  'SaveUserAccess')->name('SaveUserAccess');
            Route::get('/refreshRooms','refreshRooms')->name('refreshRooms');
            Route::get('/delete-user','deleteUser')->name('delete-user');
            Route::post('/add-config', 'addConfig')->name('addConfig');
        });
        // ticketing
        Route::get('/create-device-ticket', [TicketController::class, 'createDeviceTticket'])->name('createDeviceTticket');
        Route::get('/all-ticket', [TicketController::class, 'allTicket'])->name('all-ticket');
        Route::get('/edit-config', [SystemConfig::class, 'editConfig'])->name('edit-config');
        // Route::get('/fetch-config', [SystemConfig::class, 'SystemVal'])->name('SystemVal');
        Route::post('/update-config', [SystemConfig::class, 'updateConfig'])->name('update-config');

    });
});
Route::middleware('auth')->group(function () {
    Route::post('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::post('/createLogFile',[FileController::class,'createLogFile'])->name('createLogFile');
Route::post('/storeLogFile',[FileController::class,'storeLogFile'])->name('storeLogFile');

// Route::get('/email',function(){
//     Mail::to('mikael.lazaro@esco.com.ph')->send(new WelcomeMail());
//     return new WelcomeMail();
// }); 
require __DIR__ . '/auth.php';