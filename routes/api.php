<?php

use App\Http\Controllers\Api\StoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\PostController;

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


Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('/users', [AuthController::class, 'index']);
        Route::get('/getProfile', [AuthController::class, 'getProfile']);
        Route::post('/updateProfile', [AuthController::class, 'updateProfile']);

        Route::get('/images/{filename}', [AuthController::class, 'images']);

        Route::get('/suggestFriends', [FriendController::class, 'suggestFriends']);
        Route::get('/getFriends', [FriendController::class, 'getFriends']);
        Route::get('/getFriendRequest', [FriendController::class, 'getFriendRequest']);
        Route::get('/getSendFriendRequest', [FriendController::class, 'getSendFriendRequest']);
        Route::post('/sendRequest', [FriendController::class, 'sendRequest']);
        Route::post('/acceptRequest', [FriendController::class, 'acceptRequest']);
        Route::post('/deleteRequest', [FriendController::class, 'deleteRequest']);

        Route::post('/addStory', [StoryController::class, 'addStory']);
        Route::get('/viweFriendStory', [StoryController::class, 'viweFriendStory']);
        Route::get('/stories/{filename}', [AuthController::class, 'stories']);

        Route::post('/addPost', [PostController::class, 'addPost']);
        Route::get('/viewFriendPost', [PostController::class, 'viewFriendPost']);
        Route::get('/posts/{filename}', [AuthController::class, 'posts']);
});
