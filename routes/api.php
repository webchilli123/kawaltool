<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('test', function () {
    return 'API Working';
});

// login
Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
// Route::prefix('v1')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('source', [CommonController::class, 'source']);
    Route::get('party', [CommonController::class, 'party']);
    Route::get('level', [CommonController::class, 'level']);
    Route::get('product', [ProductController::class, 'product']);
    Route::get('user', [CommonController::class, 'user']);
    Route::get('status', [CommonController::class, 'status']);
    Route::get('follow-up-types', [CommonController::class, 'followUpTypes']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // leads
    Route::prefix('lead')->group(function () {
        Route::get('create', [LeadController::class, 'create']);
        Route::get('index', [LeadController::class, 'index']);
        Route::post('store', [LeadController::class, 'store']);
        Route::get('edit/{id}', [LeadController::class, 'edit']);
        Route::put('update/{id}', [LeadController::class, 'update']);
        Route::delete('delete/{id}', [LeadController::class, 'destroy']);
        Route::put('update-followup/{id}', [LeadController::class, 'updateFollowupApi']);
    });

    // complaint
    Route::prefix('complaint')->group(function () {
        Route::get('create', [ComplaintController::class, 'create']);
        Route::get('index', [ComplaintController::class, 'index']);
        Route::post('store', [ComplaintController::class, 'store']);
        Route::get('edit/{id}', [ComplaintController::class, 'edit']);
        Route::put('update/{id}', [ComplaintController::class, 'update']);
        Route::delete('delete/{id}', [ComplaintController::class, 'destroy']);
    });

    Route::get('/get-party-products/{party}', [ComplaintController::class, 'getPartyProducts']);

    
});
