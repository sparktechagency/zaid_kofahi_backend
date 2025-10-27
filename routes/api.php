<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Organizer\EventController;
use App\Http\Controllers\Api\Organizer\TransactionController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StaticPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// social login (google)
Route::post('/social-login', [AuthController::class, 'socialLogin']);

// static page show
Route::get('pages/{slug?}', [StaticPageController::class, 'show']);

// check token valid
Route::get('/check-token',[AuthController::class,'checkToken']);


Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/get-profile', [AuthController::class, 'getProfile']);
    Route::post('/edit-profile', [SettingsController::class, 'editProfile']);
    Route::post('/update-password', [AuthController::class, 'updatePassword']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);

    // static page update
    Route::post('pages/{slug?}', [StaticPageController::class, 'update']);

    // notification
    Route::get('/get-notifications', [NotificationController::class, 'getNotifications']);
    Route::patch('/read', [NotificationController::class, 'read']);
    Route::patch('/read-all', [NotificationController::class, 'readAll']);
    Route::get('/notification-status', [NotificationController::class, 'status']);
    

    Route::middleware('admin')->prefix('admin')->group(function () {
        //
    });

    Route::middleware('finance')->prefix('finance')->group(function () {
        //
    });

    Route::middleware('support')->prefix('support')->group(function () {
        //
    });

    Route::middleware('player')->prefix('player')->group(function () {
        //
    });

    Route::middleware('organizer')->prefix('organizer')->group(function () {
        // event
        Route::post('/create-event',[EventController::class,'createEvent']);
        Route::get('/get-events',[EventController::class,'getEvents']);
        Route::get('/view-event/{id?}',[EventController::class,'viewEvent']);
        Route::patch('/edit-event/{id?}',[EventController::class,'editEvent']);
        Route::delete('/delete-event/{id?}',[EventController::class,'deleteEvent']);

        // trnasaction
        Route::post('/deposit',[TransactionController::class,'deposit']);
    });

    Route::middleware('admin.finance')->prefix('admin-finance')->group(function () {
        //
    });

    Route::middleware('admin.support')->prefix('admin-support')->group(function () {
        //
    });

    Route::middleware('admin.finance.support')->prefix('admin-finance-support')->group(function () {
        //
    });

});

