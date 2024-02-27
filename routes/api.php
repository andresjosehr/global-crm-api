<?php

use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\OrdersCoursesController;
use App\Http\Controllers\ProcessesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\Traking\CertificationTestsController;
use App\Http\Controllers\Traking\SapInstalationsController;
use App\Http\Controllers\Traking\SapTriesController;
use App\Http\Controllers\UsersActivitiesControllers;
use App\Http\Controllers\UsersController;
use App\Http\Services\ImportStudentsService;
use App\Http\Services\ImportStudentsServiceCO;
use App\Http\Services\ImportStudentsServiceSEG;
use App\Models\SapInstalation;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use PhpParser\Node\Expr\Assign;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$basePathController = 'App\Http\Controllers\\';

Route::get('auth/check-instalation-sap-schedule-access/{key}', 'App\Http\Controllers\Traking\SapInstalationsController@checkScheduleAccess');
Route::get('traking/sap-instalations/{key}', [SapInstalationsController::class, 'getSapInstalation']);

Route::prefix('auth')->group(function () {
    Route::post('sign-in', [AuthController::class, 'signIn'])->name('auth.sign-in');
    Route::post('sign-in-enrollment/{order_jey}', [AuthController::class, 'signInEnrollment'])->name('auth.sign-in-enrollment');
    Route::post('check-auth', [AuthController::class, 'checkAuth'])->name('auth.check-auth')->middleware(['api_access']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('passwords.sent');
    Route::post('check-password-reset-token', [AuthController::class, 'checkPasswordResetToken']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('messages/extension', 'App\Http\Controllers\Messages\FreeCourseExtensions@index');
Route::get('messages/ponderacion', 'App\Http\Controllers\Messages\FreeCoursesWeightedController@index');

Route::post('sales/call-activity', 'App\Http\Controllers\PusherController@callActivity');
Route::post('sales/disconnect-call-activity', 'App\Http\Controllers\LeadsController@diconnectCallActivity');

Route::group(['middleware' => ['api_access']], function () use ($basePathController) {

    Route::resource('users-activities', UsersActivitiesControllers::class);

    Route::get('assignments', [AssignmentsController::class, 'index']);
    Route::put('assignments/{id}', [AssignmentsController::class, 'update']);

    Route::get('make-session/{id}', 'App\Http\Controllers\AuthController@makeSession');
    Route::get('notifications', 'App\Http\Controllers\NotificationController@index');
    Route::put('notifications/{id}', 'App\Http\Controllers\NotificationController@update');


    Route::post('users/toggle-status', 'App\Http\Controllers\UsersController@toggleStatus');
    Route::get('users/get-list', [UsersController::class, 'getList']);
    Route::put('users/toggle-status/{id}', [UsersController::class, 'toggleStatus']);
    Route::post('users/save', [UsersController::class, 'save']);
    Route::post('users/update/{id}', [UsersController::class, 'update']);
    Route::get('users/get/{id}', [UsersController::class, 'get']);


    Route::post('orders/update-traking-info/{id}', 'App\Http\Controllers\OrdersController@updateTrakingInfo');
    Route::get('orders/{id}/dates-history', 'App\Http\Controllers\OrdersController@datesHistory');
    Route::resource('students', StudentsController::class);
    Route::put('students/delegate-academic-area/{id}', [StudentsController::class, 'delegateAcademicArea']);

    Route::resource('orders', OrdersController::class);
    Route::resource('order-courses', OrdersCoursesController::class);
    Route::get('student-orders/get-options', 'App\Http\Controllers\OrdersController@getOptions');

    Route::resource('dues', 'App\Http\Controllers\DuesController');
    Route::resource('orders-courses', 'App\Http\Controllers\OrdersCoursesController');

    Route::resource('messages', 'App\Http\Controllers\MessagesController');


    Route::prefix('sales')->group(function () {
        Route::post('import-data', 'App\Http\Controllers\LeadProjectsController@importData');
        Route::post('update-user-projects', 'App\Http\Controllers\LeadProjectsController@updateUserProjects');
        Route::get('get-projects', 'App\Http\Controllers\LeadProjectsController@getProjects');
        Route::get('get-leads/{mode?}', 'App\Http\Controllers\LeadsController@getLeads');
        Route::get('get-lead/{id}', 'App\Http\Controllers\LeadsController@getLead');
        Route::get('get-lead-by-phone/{phone}', 'App\Http\Controllers\LeadsController@getLeadByPhone');
        Route::get('get-zadarma-info', 'App\Http\Controllers\SalesController@getZadarmaInfo');
        Route::get('get-next-lead', 'App\Http\Controllers\LeadsController@getNextLead');
        Route::get('archive-lead/{id}', 'App\Http\Controllers\LeadsController@archiveLead');
        Route::post('create-lead', 'App\Http\Controllers\LeadsController@createLead');
        Route::post('archive-leads-by-batch', 'App\Http\Controllers\LeadsController@archiveLeadByBatch');

        Route::get('get-previous-lead', 'App\Http\Controllers\LeadsController@getPreviousLead');
        Route::get('get-current-lead', 'App\Http\Controllers\LeadsController@getCurrentLead');
        Route::get('get-manage-lead-options', 'App\Http\Controllers\LeadsController@getManageLeadOptions');
        Route::get('get-sells-users', 'App\Http\Controllers\UsersController@getSellsUsers');
        Route::post('update-sales-activity', 'App\Http\Controllers\LeadsController@updateSalesActivity');
        Route::get('last-call-activity', 'App\Http\Controllers\LeadsController@getLastCallActivity');
        Route::post('create-student-from-lead/{id}/{lead_assignment_id?}', 'App\Http\Controllers\LeadsController@createStudentFromLead');


        Route::get('get-next-schedule-call', 'App\Http\Controllers\LeadsController@getNextScheduleCall');




        Route::post('save-observation/{id}/{leadAssignamentId?}', 'App\Http\Controllers\LeadsController@saveObservation');
        Route::post('save-basic-data/{id}', 'App\Http\Controllers\LeadsController@saveBasicData');


        Route::prefix('activity')->group(function () {

            Route::get('get-leads-assignments', 'App\Http\Controllers\LeadsController@getLeadsAssignments');
            Route::get('get-assignments-by-hour', 'App\Http\Controllers\ReportsController@getAssignmentsByHour');

            Route::get('get-calls', 'App\Http\Controllers\LeadsController@getCalls');
            Route::get('get-calls-by-hour', 'App\Http\Controllers\ReportsController@getCallsByHour');

            Route::get('get-sales-stats', [ReportsController::class, 'getSalesStats']);
            Route::get('get-stats-per-day', [ReportsController::class, 'getStatsPerDay']);
            Route::get('get-stats-per-hour', [ReportsController::class, 'getStatsPerHour']);
        });
    });

    Route::prefix('traking')->group(function () {
        Route::prefix('sap-instalations')->group(function () {
            Route::get('list', [SapInstalationsController::class, 'getList']);
            Route::get('get-from-order/{id}', [SapInstalationsController::class, 'getFromOrder']);

            Route::post('save-draft', [SapInstalationsController::class, 'saveDraft']);
            Route::put('update/{id}', [SapInstalationsController::class, 'update']);
            Route::put('update-from-student/{id}', [SapInstalationsController::class, 'updateFromStudent']);


            Route::get('options', [SapInstalationsController::class, 'getOptions']);
            Route::get('get-available-times/{date}', [SapInstalationsController::class, 'getAvailableTimes']);
            Route::put('verified-payment/{id}', [SapInstalationsController::class, 'verifiedPayment']);
            Route::get('sap-tries/{sap_id}', [SapTriesController::class, 'getSapTries']);
            Route::get('sap-try/{sap_id}', [SapTriesController::class, 'getSapTry']);
            Route::put('sap-try/{id}', [SapTriesController::class, 'update']);
            Route::put('sap-try/verified-payment/{id}', [SapTriesController::class, 'verifiedPayment']);



            Route::put('update-payment/{id}', [SapInstalationsController::class, 'updatePayment']);
            Route::put('sap-try/update-payment/{id}', [SapTriesController::class, 'updatePayment']);


            Route::get('{id}', [SapInstalationsController::class, 'getSapInstalation']);
        });

        Route::prefix('certification-tests')->group(function () {
            Route::put('update', [CertificationTestsController::class, 'update']);
        });
        Route::prefix('extensions')->group(function () {
            Route::post('save-draft', 'App\Http\Controllers\Traking\ExtensionsController@saveDraft');
            Route::put('update', 'App\Http\Controllers\Traking\ExtensionsController@update');
        });

        Route::prefix('freezings')->group(function () {
            Route::post('save-draft', 'App\Http\Controllers\Traking\FreezingsController@saveDraft');
            Route::put('update', 'App\Http\Controllers\Traking\FreezingsController@update');
            Route::get('unfreeze-course/{id}', 'App\Http\Controllers\Traking\FreezingsController@unfreezeCourse');
        });
    });
    /* Add new routes here */
    Route::get('get-roles', [OptionsController::class, 'getRoles']);
});

Route::resource('document-types', 'App\Http\Controllers\DocumentTypesController');

Route::get('users/find-available-staff/{date}', 'App\Http\Controllers\Traking\SapInstalationsController@findAvailableStaff');

Route::get('auth/check-term-access/{key}', 'App\Http\Controllers\StudentsController@checkTermsAccess');
Route::get('terms-info/{key}', 'App\Http\Controllers\StudentsController@getTermsInfo');
Route::post('terms-pdf-template/{order_id}', 'App\Http\Controllers\StudentsController@saveTermsPdfTemplate');
Route::get('download-terms-pdf-template/{order_id}', 'App\Http\Controllers\StudentsController@downloadTermsPdfTemplate');
Route::post('terms-info/{key}/confirm', 'App\Http\Controllers\StudentsController@confirmTermsInfo');



Route::get('import', 'App\Http\Controllers\ImportContorller@index');
Route::get('countries', [CountriesController::class, 'index']);
Route::get('get-currencies', [OptionsController::class, 'getCurrencies']);
Route::get('get-schedule-sap-prices', [OptionsController::class, 'getScheduleSapPrices']);
Route::get('get-payment-methods', [OptionsController::class, 'getPaymentMethods']);
Route::get('get-holidays', [OptionsController::class, 'getHolidays']);


Route::get('get-state-by-country/{country_id}', 'App\Http\Controllers\CountriesController@getStateByCountry');
Route::get('get-city-by-state/{state_id}', 'App\Http\Controllers\CountriesController@getCityByState');
Route::get('get-city/{id}', 'App\Http\Controllers\CountriesController@getCity');
Route::get('get-state/{id}', 'App\Http\Controllers\CountriesController@getState');
Route::get('test', 'App\Http\Controllers\TestController@index');
Route::get('mail', 'App\Http\Controllers\MailsController@index');






Route::group(['middleware' => ['environment_access']], function () use ($basePathController) {
    Route::prefix('processes')->group(function () {
        Route::get('update-test-status', 'App\Http\Controllers\ProcessesController@updateTestsStatus');
        Route::get('update-courses-status', 'App\Http\Controllers\ProcessesController@updateCoursesStatus');
        Route::get('update-excel-mails', 'App\Http\Controllers\ProcessesController@updateExcelMails');
        Route::get('update-aula-status', 'App\Http\Controllers\ProcessesController@updateAulaStatus');
        Route::get('update-unfreezing-texts', 'App\Http\Controllers\ProcessesController@updateUnfreezingTexts');

        Route::get('update-texts', 'App\Http\Controllers\ProcessesController@updateTexts');
        Route::get('update-abandoned', 'App\Http\Controllers\ProcessesController@updateAbandoned');
        Route::get('update-complete-free-courses-text', 'App\Http\Controllers\ProcessesController@updateCompleteFreeCoursesText');

        Route::post('import-leads-from-liveconnect', 'App\Http\Controllers\ProcessesController@importLeadsFromLiveconnect');
        Route::get('free-courses-text', 'App\Http\Controllers\ProcessesController@freeCoursesTexts');

        Route::get('update-complete-free-courses-onemonth', 'App\Http\Controllers\ProcessesController@updatecompletefreecoursesonemonth');

        Route::get('import-seg',  [ImportStudentsServiceSEG::class, 'index']);
        Route::get('import-co',  [ImportStudentsServiceCO::class, 'index']);
    });

    Route::prefix('mails')->group(function () {
        Route::get('send-unfreezings-emails', 'App\Http\Controllers\ProcessesController@sendUnfreezingsEmails');
    });
});
Route::prefix('processes')->group(function () {
    Route::post('import-leads-from-liveconnect', 'App\Http\Controllers\ProcessesController@importLeadsFromLiveconnect');
    Route::post('generate-message', 'App\Http\Controllers\ProcessesController@generateMessage');
});


Route::group(['middleware' => ['environment_access']], function () use ($basePathController) {
    Route::get('bk', 'App\Http\Controllers\ProcessesController@getBkFiles');
    Route::get('bk/{file}', 'App\Http\Controllers\ProcessesController@downloadBkFile');
});

Route::prefix('traking')->group(function () {
    Route::prefix('sap-instalations')->group(function () {
        Route::get('get-sap-instalation/{key}', [SapInstalationsController::class, 'getSapInstalation']);
    });
});

Route::post('users/{id}/get-available-times', 'App\Http\Controllers\Traking\SapInstalationsController@getAvailableTimes');

Route::post('toggle-user-working-status', [ProcessesController::class, 'toggleUserWorkingStatus']);
Route::get('toggle-user-working-status', [ProcessesController::class, 'toggleUserWorkingStatus']);
