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

Route::group(['middleware' => ['api_access']], function () use ($basePathController) {
    Route::resource('cars', 'App\Http\Controllers\CarsController');
	Route::get('get-all-cars', 'App\Http\Controllers\CarsController@getAll');

    Route::post('users/{id}/get-available-times', 'App\Http\Controllers\UsersController@getAvailableTimes');
    Route::post('orders/update-traking-info/{id}', 'App\Http\Controllers\OrdersController@updateTrakingInfo');
	Route::resource('students', 'App\Http\Controllers\StudentsController');
    Route::resource('orders', 'App\Http\Controllers\OrdersController');
    Route::get('student-orders/get-options', 'App\Http\Controllers\OrdersController@getOptions');

    Route::resource('dues', 'App\Http\Controllers\DuesController');
    Route::resource('orders-courses', 'App\Http\Controllers\OrdersCoursesController');

    Route::resource('messages', 'App\Http\Controllers\MessagesController');


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
