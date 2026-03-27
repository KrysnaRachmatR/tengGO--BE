<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CrewController;
use App\Http\Controllers\Api\FleetController;
use App\Http\Controllers\Api\PoController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\SeriesScheduleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TengGO! API Routes — v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------------------
    // Auth — public
    // -------------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // -------------------------------------------------------------------------
    // Protected — butuh Sanctum token
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',     [AuthController::class, 'logout']);
            Route::get('me',          [AuthController::class, 'me']);
            Route::put('me/password', [AuthController::class, 'updatePassword']);
        });

        // -------------------------------------------------------------------------
        // Super Admin — manajemen PO & Admin PO
        // -------------------------------------------------------------------------
        Route::prefix('admin')->group(function () {
            Route::get('pos',               [PoController::class, 'index']);
            Route::post('pos',              [PoController::class, 'store']);
            Route::get('pos/{po}',          [PoController::class, 'show']);
            Route::put('pos/{po}',          [PoController::class, 'update']);
            Route::delete('pos/{po}',       [PoController::class, 'destroy']);
            Route::get('pos/{po}/users',    [PoController::class, 'users']);
            Route::post('pos/{po}/admin',   [PoController::class, 'storeAdmin']);
        });

        // -------------------------------------------------------------------------
        // Admin PO — manajemen user/staff
        // -------------------------------------------------------------------------
        Route::prefix('po')->group(function () {
            Route::get('users',           [UserController::class, 'index']);
            Route::post('users',          [UserController::class, 'store']);
            Route::get('users/{user}',    [UserController::class, 'show']);
            Route::put('users/{user}',    [UserController::class, 'update']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);
        });

        // -------------------------------------------------------------------------
        // Data Master — Routes
        // -------------------------------------------------------------------------
        Route::get('routes',          [RouteController::class, 'index']);
        Route::post('routes',         [RouteController::class, 'store']);
        Route::get('routes/{route}',  [RouteController::class, 'show']);
        Route::put('routes/{route}',  [RouteController::class, 'update']);
        Route::delete('routes/{route}', [RouteController::class, 'destroy']);

        // Data Master — Series (nested di bawah route untuk store, standalone untuk yang lain)
        Route::get('routes/{route}/series',  [SeriesController::class, 'index']);
        Route::post('routes/{route}/series', [SeriesController::class, 'store']);
        Route::get('series/{series}',        [SeriesController::class, 'show']);
        Route::put('series/{series}',        [SeriesController::class, 'update']);
        Route::delete('series/{series}',     [SeriesController::class, 'destroy']);

        // Data Master — Series Schedules
        Route::get('series/{series}/schedules',              [SeriesScheduleController::class, 'index']);
        Route::post('series/{series}/schedules/bulk',        [SeriesScheduleController::class, 'bulk']);
        Route::patch('series/{series}/schedules/{date}',     [SeriesScheduleController::class, 'toggle']);
        Route::delete('series/{series}/schedules/{date}',    [SeriesScheduleController::class, 'destroy']);

        // Data Master — Fleets
        Route::get('fleets',             [FleetController::class, 'index']);
        Route::post('fleets',            [FleetController::class, 'store']);
        Route::get('fleets/{fleet}',     [FleetController::class, 'show']);
        Route::put('fleets/{fleet}',     [FleetController::class, 'update']);
        Route::delete('fleets/{fleet}',  [FleetController::class, 'destroy']);
        Route::put('fleets/{fleet}/seat-config',  [FleetController::class, 'updateSeats']);
        Route::put('fleets/{fleet}/facilities',   [FleetController::class, 'updateFacilities']);

        // Data Master — Crews
        Route::get('crews',           [CrewController::class, 'index']);
        Route::post('crews',          [CrewController::class, 'store']);
        Route::get('crews/{crew}',    [CrewController::class, 'show']);
        Route::put('crews/{crew}',    [CrewController::class, 'update']);
        Route::delete('crews/{crew}', [CrewController::class, 'destroy']);

        // -------------------------------------------------------------------------
        // Trip Harian — akan dibuat di iterasi berikutnya
        // -------------------------------------------------------------------------
        Route::get('trips',                        [TripController::class, 'index']);
        Route::post('trips',                       [TripController::class, 'store']);
        Route::post('trips/generate',              [TripController::class, 'generate']);
        Route::get('trips/{trip}',                 [TripController::class, 'show']);
        Route::put('trips/{trip}',                 [TripController::class, 'update']);
        Route::patch('trips/{trip}/status',        [TripController::class, 'updateStatus']);
        Route::delete('trips/{trip}',              [TripController::class, 'destroy']);
        Route::post('trips/{trip}/crews',          [TripController::class, 'assignCrew']);
        Route::delete('trips/{trip}/crews/{crew}', [TripController::class, 'removeCrew']);
    });
});