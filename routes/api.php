<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArmadaController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\TripController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (NO LOGIN)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */
    Route::get('/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | ARMADA
    |--------------------------------------------------------------------------
    */
    Route::prefix('armadas')->group(function () {
        Route::get('/', [ArmadaController::class, 'index']);
        Route::post('/', [ArmadaController::class, 'store']);
        Route::get('/{id}', [ArmadaController::class, 'show']);
        Route::put('/{id}', [ArmadaController::class, 'update']);
        Route::delete('/{id}', [ArmadaController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | ROUTES (JALUR)
    |--------------------------------------------------------------------------
    */
    Route::prefix('routes')->group(function () {
        Route::get('/', [RouteController::class, 'index']);
        Route::post('/', [RouteController::class, 'store']);
        Route::get('/{id}', [RouteController::class, 'show']);
        Route::put('/{id}', [RouteController::class, 'update']);
        Route::delete('/{id}', [RouteController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | SERIES
    |--------------------------------------------------------------------------
    */
    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'index']);
        Route::post('/', [SeriesController::class, 'store']);
        Route::get('/{id}', [SeriesController::class, 'show']);
        Route::put('/{id}', [SeriesController::class, 'update']);
        Route::delete('/{id}', [SeriesController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | TRIPS (CORE 🔥)
    |--------------------------------------------------------------------------
    */
    Route::prefix('trips')->group(function () {
        Route::get('/', [TripController::class, 'index']);
        Route::post('/', [TripController::class, 'store']);
        Route::post('/bulk', [TripController::class, 'bulkStore']); // 🔥 multi create
        Route::get('/{id}', [TripController::class, 'show']);
        Route::put('/{id}', [TripController::class, 'update']);
        Route::delete('/{id}', [TripController::class, 'destroy']);
    });

});