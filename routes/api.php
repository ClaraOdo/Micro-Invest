<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\InvestmentPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello Engine',
    ]);
});

// public endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// private endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/investment-plans', [InvestmentPlanController::class, 'index']);
    Route::get('/investment-plans/{plan}', [InvestmentPlanController::class, 'show']);

    Route::post('/investments', [InvestmentController::class, 'store']);

    Route::get('/investments', [InvestmentController::class, 'index']);

    Route::get('/investments/{investment}', [InvestmentController::class, 'show']);

    Route::post('/investments/{investment}/withdrawal', function () {
        return response()->json([
            'message' => 'Implement specific investment withdrawal, send an email notification, compute right accrued_interest',
        ]);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
