<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InviteController;
use App\Http\Controllers\API\UsersController;
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

Route::post('login', [AuthController::class, 'login']);

Route::post('/registration/{token}', [UsersController::class, 'registration'])->name('registration');
Route::post('/registration', [UsersController::class, 'create'])->name('user.create');
Route::post('send/code', [UsersController::class, 'sendCode'])->name('sendCode');
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('users', [UsersController::class, 'users']);
    Route::post('invite', [InviteController::class, 'index'])->name('invite');
    Route::post('profile', [UsersController::class, 'profileUpdate'])->name('profile.update');
    Route::get('logout', [AuthController::class, 'logout']);
});
