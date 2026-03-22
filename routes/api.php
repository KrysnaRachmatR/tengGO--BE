<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PublicTripController;
use App\Http\Controllers\Api\ArmadaController;


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (NO LOGIN)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // COMPANY (SUPER ADMIN)
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
    });

    // ROLE
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    // USERS
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);

        Route::post('/{id}/assign-role', [UserController::class, 'assignRole']);
        Route::post('/{id}/remove-role', [UserController::class, 'removeRole']);
    });
    /*
    |--------------------------------------------------------------------------
    | ROUTES (MASTER)
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
    | SERIES (MASTER JADWAL)
    |--------------------------------------------------------------------------
    */
    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'index']);
        Route::post('/', [SeriesController::class, 'store']);
        Route::get('/{id}', [SeriesController::class, 'show']);
        Route::put('/{id}', [SeriesController::class, 'update']);
        Route::delete('/{id}', [SeriesController::class, 'destroy']);

        // 🔁 toggle active
        Route::patch('/{id}/toggle', [SeriesController::class, 'toggle']);
    });

    /*
    |--------------------------------------------------------------------------
    | SCHEDULES (🔥 CORE SYSTEM)
    |--------------------------------------------------------------------------
    */
    Route::prefix('schedules')->group(function () {
        Route::post('/', [ScheduleController::class, 'store']);
        Route::post('/bulk', [ScheduleController::class, 'bulkStore']);
        Route::get('/by-date', [ScheduleController::class, 'byDate']);
        Route::get('/{id}', [ScheduleController::class, 'show']);
        Route::delete('/{id}', [ScheduleController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | TRIPS (OPERASIONAL)
    |--------------------------------------------------------------------------
    */
    Route::prefix('trips')->group(function () {
        Route::post('/', [TripController::class, 'store']);
        Route::post('/bulk', [TripController::class, 'bulkStore']);
        Route::get('/', [TripController::class, 'index']);
        Route::patch('/{id}/status', [TripController::class, 'updateStatus']);
        Route::delete('/{id}', [TripController::class, 'destroy']);
    });
    /*
    |--------------------------------------------------------------------------
    | ARMADA (OPERASIONAL)
    |--------------------------------------------------------------------------
    */
    Route::prefix('armada')->group(function () {
        Route::post('/', [ArmadaController::class, 'store']);
        Route::get('/', [ArmadaController::class, 'index']);
        Route::get('/{id}', [ArmadaController::class, 'update']);
        Route::delete('/{id}', [ArmadaController::class, 'destroy']);
    });

});

Route::get('/taek', [PublicTripController::class, 'index']);