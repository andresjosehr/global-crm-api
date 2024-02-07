<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    Route::get('make-session/{id}', 'App\Http\Controllers\AuthController@makeSession');
    Route::get('notifications', 'App\Http\Controllers\NotificationController@index');
    Route::put('notifications/{id}', 'App\Http\Controllers\NotificationController@update');

    Route::post('users/{id}/get-available-times', 'App\Http\Controllers\UsersController@getAvailableTimes');
    Route::post('users/toggle-status', 'App\Http\Controllers\UsersController@toggleStatus');
    Route::post('orders/update-traking-info/{id}', 'App\Http\Controllers\OrdersController@updateTrakingInfo');
    Route::get('orders/{id}/dates-history', 'App\Http\Controllers\OrdersController@datesHistory');
    Route::resource('students', 'App\Http\Controllers\StudentsController');
    Route::resource('orders', 'App\Http\Controllers\OrdersController');
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
        Route::get('get-sells-users', 'App\Http\Controllers\UsersController@getSellsUsers');
        Route::post('update-sales-activity', 'App\Http\Controllers\LeadsController@updateSalesActivity');
        Route::get('last-call-activity', 'App\Http\Controllers\LeadsController@getLastCallActivity');
        Route::post('create-student-from-lead/{id}/{lead_assignment_id}', 'App\Http\Controllers\LeadsController@createStudentFromLead');


        Route::get('get-next-schedule-call', 'App\Http\Controllers\LeadsController@getNextScheduleCall');




        Route::post('save-observation/{id}/{leadAssignamentId?}', 'App\Http\Controllers\LeadsController@saveObservation');
        Route::post('save-basic-data/{id}', 'App\Http\Controllers\LeadsController@saveBasicData');


        Route::prefix('activity')->group(function () {

            Route::get('get-leads-assignments', 'App\Http\Controllers\LeadsController@getLeadsAssignments');
            Route::get('get-assignments-by-hour', 'App\Http\Controllers\LeadsController@getAssignmentsByHour');

            Route::get('get-calls', 'App\Http\Controllers\LeadsController@getCalls');
            Route::get('get-calls-by-hour', 'App\Http\Controllers\LeadsController@getCallsByHour');

            Route::get('get-main-stats', 'App\Http\Controllers\LeadsController@getMainStats');
        });
    });

    Route::prefix('traking')->group(function () {
        Route::prefix('sap-instalations')->group(function () {
            Route::post('save-draft', 'App\Http\Controllers\Traking\SapInstalationsController@saveDraft');
            Route::put('update/{id}', 'App\Http\Controllers\Traking\SapInstalationsController@update');
        });

        Route::prefix('certification-tests')->group(function () {
            Route::put('update', 'App\Http\Controllers\Traking\CertificationTestsController@update');
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
});

Route::resource('document-types', 'App\Http\Controllers\DocumentTypesController');

Route::get('users/find-available-staff/{date}', 'App\Http\Controllers\UsersController@findAvailableStaff');

Route::get('auth/check-term-access/{key}', 'App\Http\Controllers\StudentsController@checkTermsAccess');
Route::get('terms-info/{key}', 'App\Http\Controllers\StudentsController@getTermsInfo');
Route::post('terms-pdf-template/{order_id}', 'App\Http\Controllers\StudentsController@saveTermsPdfTemplate');
Route::get('download-terms-pdf-template/{order_id}', 'App\Http\Controllers\StudentsController@downloadTermsPdfTemplate');
Route::post('terms-info/{key}/confirm', 'App\Http\Controllers\StudentsController@confirmTermsInfo');


Route::get('import', 'App\Http\Controllers\ImportContorller@index');
Route::get('countries', 'App\Http\Controllers\CountriesController@index');
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
    });

    Route::prefix('mails')->group(function () {
        Route::get('send-unfreezings-emails', 'App\Http\Controllers\ProcessesController@sendUnfreezingsEmails');
    });
});
Route::prefix('processes')->group(function () {
    Route::post('import-leads-from-liveconnect', 'App\Http\Controllers\ProcessesController@importLeadsFromLiveconnect');
    Route::post('generate-message', 'App\Http\Controllers\ProcessesController@generateMessage');
});
