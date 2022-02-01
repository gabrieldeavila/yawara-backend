<?php

use App\Http\Controllers\HistoriesController;
use App\Http\Controllers\InteractionsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UserController;
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

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('admin-login', [RegisterController::class, 'adminLogin']);

Route::post('forgot-password', [RegisterController::class, 'forgotPassword']);
Route::post('update-password', [RegisterController::class, 'updatePassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::resource('teste', [UserController::class, 'index']);
    Route::middleware('admin')->group(function () {
        Route::get('admin/tags', [TagsController::class, 'index']);
        Route::post('admin/tags', [TagsController::class, 'store']);
        Route::delete('admin/tags', [TagsController::class, 'destroy']);
        Route::post('admin/tags/edit', [TagsController::class, 'update']);
        Route::post('admin/search-for', [UserController::class, 'search']);
    });

    Route::get('isLogged', function () {
        return response()->json([
            'success' => Auth::user(),
        ]);
    });

    Route::middleware('user')->group(function () {
        Route::post('finish-registration', [RegisterController::class, 'finishRegistration']);
        Route::get('hasFinished', [RegisterController::class, 'hasFinished']);
        Route::get('tags', [TagsController::class, 'index']);
        Route::get('profile', [UserController::class, 'show']);
        Route::post('search-for', [HistoriesController::class, 'search']);
        Route::post('profile', [UserController::class, 'update']);
        Route::post('my-histories', [HistoriesController::class, 'index']);
        Route::post('new-history', [HistoriesController::class, 'store']);
        Route::post('explore', [HistoriesController::class, 'explore']);
        Route::get('view/{history}', [HistoriesController::class, 'show']);
        Route::post('interaction', [InteractionsController::class, 'store']);
        Route::post('history/answer', [HistoriesController::class, 'add']);
    });

});
