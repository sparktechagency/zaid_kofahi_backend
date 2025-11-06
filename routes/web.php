<?php

use App\Http\Controllers\Api\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/connected', [StripeController::class, 'handleConnectedAccount']);