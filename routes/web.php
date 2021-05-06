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

Route::post('login', 'LoginController@login');
Route::get('{provider}/login', 'LoginController@index');
Route::get('login/{provider}', 'LoginController@redirectToProvider');
Route::get('{provider}/callback', 'LoginController@handleProviderCallback');
Route::get('/home', function () {
    return 'User is logged in';
});

Route::group(['namespace' => 'Api\V1'], function ($router) {
    Route::group(['prefix' => 'users'], function ($router) {

        Route::get('/verify/{user}', 'VerificationController@verify')->name('verification.verify');

    });

});