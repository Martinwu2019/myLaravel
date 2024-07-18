<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('user', [AuthController::class,'me']);
Route::put('user/password', [AuthController::class, 'changePassword']);

Route::post('login', [AuthController::class,'login']);
Route::post('signup', [AuthController::class,'signup']);

Route::group(['middleware' => ['auth:api']], function () {
    // Route::get('user', [AuthController::class, 'me']);
    // Route::put('user/password', [AuthController::class, 'changePassword']);
});

// Route::group(['middleware' => 'api'], function ($router) {
//     Route::put('user/password', [AuthController::class, 'changePassword']);
//     // Route::post('logout', [AuthController::class,'logout']);
//     // Route::post('refresh', [AuthController::class,'refresh']);
//     // Route::post('me', 'AuthController@me');
// });

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
