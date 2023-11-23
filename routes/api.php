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

Route::prefix('auth')->group(function (){
    Route::post('sign-in', [AuthController::class, 'signIn'])->name('auth.sign-in');
    Route::post('check-auth', [AuthController::class, 'checkAuth'])->name('auth.check-auth')->middleware(['api_access']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('passwords.sent');
    Route::post('check-password-reset-token', [AuthController::class, 'checkPasswordResetToken']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('messages/extension', 'App\Http\Controllers\Messages\FreeCourseExtensions@index');
Route::get('messages/ponderacion', 'App\Http\Controllers\Messages\FreeCoursesWeightedController@index');

Route::group(['middleware' => ['api_access']], function () use ($basePathController) {
    Route::resource('cars', 'App\Http\Controllers\CarsController');
	Route::get('get-all-cars', 'App\Http\Controllers\CarsController@getAll');

    Route::post('users/{id}/get-available-times', 'App\Http\Controllers\UsersController@getAvailableTimes');
    Route::post('orders/update-traking-info/{id}', 'App\Http\Controllers\OrdersController@updateTrakingInfo');
    Route::get('orders/{id}/dates-history', 'App\Http\Controllers\OrdersController@datesHistory');
	Route::resource('students', 'App\Http\Controllers\StudentsController');
    Route::resource('orders', 'App\Http\Controllers\OrdersController');
    Route::get('student-orders/get-options', 'App\Http\Controllers\OrdersController@getOptions');

    Route::resource('dues', 'App\Http\Controllers\DuesController');
    Route::resource('orders-courses', 'App\Http\Controllers\OrdersCoursesController');

    Route::resource('messages', 'App\Http\Controllers\MessagesController');


    Route::prefix('sales')->group(function (){
        Route::post('import-data', 'App\Http\Controllers\SalesController@importData');
        Route::get('get-leads/{mode?}', 'App\Http\Controllers\LeadsController@getLeads');
        Route::get('get-lead/{id}', 'App\Http\Controllers\LeadsController@getLead');
        Route::get('get-zadarma-info', 'App\Http\Controllers\SalesController@getZadarmaInfo');
        Route::get('get-next-lead', 'App\Http\Controllers\LeadsController@getNextLead');
        Route::get('get-previous-lead', 'App\Http\Controllers\LeadsController@getPreviousLead');
        Route::get('get-current-lead', 'App\Http\Controllers\LeadsController@getCurrentLead');

        Route::get('get-next-schedule-call', 'App\Http\Controllers\LeadsController@getNextScheduleCall');

        Route::get('activity-history', 'App\Http\Controllers\LeadsController@getActivityHistory');
        Route::get('activity-history-by-user', 'App\Http\Controllers\LeadsController@getActivityHistoryByUser');

        Route::post('save-observation/{id}/{leadAssignamentId?}', 'App\Http\Controllers\LeadsController@saveObservation');
        Route::post('save-basic-data/{id}', 'App\Http\Controllers\LeadsController@saveBasicData');
    });
	/* Add new routes here */
});

Route::resource('document-types', 'App\Http\Controllers\DocumentTypesController');

Route::get('users/find-available-staff/{date}', 'App\Http\Controllers\UsersController@findAvailableStaff');

Route::get('auth/check-term-access/{key}', 'App\Http\Controllers\StudentsController@checkTermsAccess');
Route::get('terms-info/{key}', 'App\Http\Controllers\StudentsController@getTermsInfo');
Route::post('terms-info/{key}/confirm', 'App\Http\Controllers\StudentsController@confirmTermsInfo');


Route::get('import', 'App\Http\Controllers\ImportContorller@index');
Route::get('countries', 'App\Http\Controllers\CountriesController@index');
Route::get('test', 'App\Http\Controllers\TestController@index');
Route::get('mail', 'App\Http\Controllers\MailsController@index');






Route::group(['middleware' => ['environment_access']], function () use ($basePathController) {
    Route::prefix('processes')->group(function (){
        Route::get('update-test-status', 'App\Http\Controllers\ProcessesController@updateTestsStatus');
        Route::get('update-courses-status', 'App\Http\Controllers\ProcessesController@updateCoursesStatus');
        Route::get('update-excel-mails', 'App\Http\Controllers\ProcessesController@updateExcelMails');
        Route::get('update-aula-status', 'App\Http\Controllers\ProcessesController@updateAulaStatus');
        Route::get('update-unfreezing-texts', 'App\Http\Controllers\ProcessesController@updateUnfreezingTexts');
        Route::get('update-texts', 'App\Http\Controllers\ProcessesController@updateTexts');
    });

    Route::prefix('mails')->group(function (){
        Route::get('send-unfreezings-emails', 'App\Http\Controllers\ProcessesController@sendUnfreezingsEmails');
    });
});
