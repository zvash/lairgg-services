<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['namespace' => 'Api\V1'], function ($router) {
    Route::group(['prefix' => 'users'], function ($router) {

        Route::get('/verify/{user}', 'VerificationController@verify')->name('verification.verify');

        Route::post('password/reset/email', 'ForgotPasswordController@reset')->name('password.reset');
        // Password Reset Routes
        Route::get('/password/reset/success', 'ResetPasswordController@success')->name('users.password.reset.success');
        Route::get('/password/reset/{token}', 'ResetPasswordController@form')->name('users.password.reset.form');
        Route::post('/password/reset', 'ResetPasswordController@reset')->name('users.password.reset.update');

    });

});