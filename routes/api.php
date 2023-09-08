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

	Route::resource('students', 'App\Http\Controllers\StudentsController');

	/* Add new routes here */
});


Route::get('import', 'App\Http\Controllers\ImportContorller@index');
