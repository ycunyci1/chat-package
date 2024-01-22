<?php

use Dd1\Chat\Controllers\ChatController;
use Dd1\Chat\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web Routes for your application. These
| Routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->prefix('api')->group(function () {
    Route::get('get-user-info', [UserController::class, 'getUserInfo']);
    Route::get('chats', [ChatController::class, 'getChats']);
    Route::post('chats', [ChatController::class, 'createChat']);
    Route::get('chats/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('chats/{chatId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('chats/{chatId}/messages/{messageId}', [ChatController::class, 'readMessage']);
    Route::post('typing', [ChatController::class, 'handleTyping']);
    Route::get('search-users', [ChatController::class, 'searchUsers']);
    Route::get('update-centrifugo-token', [UserController::class, 'updateCentrifugoToken']);
});
