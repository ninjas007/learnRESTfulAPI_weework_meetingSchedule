<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// prefix adalah untuk menambahkan endpoint versi di link
// middleware adalah untuk mengamankan menggunakan cors yang sudah diregistrasi sebelumnya di kernel.php
Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function() {

	Route::resource('meeting', 'MeetingController', ['except' => ['create', 'edit']]);

	Route::resource('meeting/registration', 'RegisterController', ['only' => ['store', 'destroy']]);

	Route::post('/user/register', ['uses' => 'AuthController@store']);

	Route::post('/user/signin', ['uses' => 'AuthController@signin']);
});

