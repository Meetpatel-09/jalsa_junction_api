<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });
    
Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/users', [AuthController::class, 'index']);

        Route::get('/suggestFriends', [FriendController::class, 'suggestFriends']);
        Route::get('/getFriends', [FriendController::class, 'getFriends']);
        Route::get('/getFriendRequest', [FriendController::class, 'getFriendRequest']);
        Route::get('/getSendFriendRequest', [FriendController::class, 'getSendFriendRequest']);
        Route::post('/sendRequest', [FriendController::class, 'sendRequest']);
        Route::post('/acceptRequest', [FriendController::class, 'acceptRequest']);
        Route::post('/deleteRequest', [FriendController::class, 'deleteRequest']);

});