<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\LocationController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function (Request $request) {
    return response()->json(
        ['test' => 'test']
    );
})->middleware('client');

Route::prefix('location')->middleware(['client'])->group(function () {
    Route::get('/', [LocationController::class, 'index']);
    Route::get('/search', [LocationController::class, 'searchLocation']);
    Route::get('/weather-forecast', [LocationController::class, 'weatherForecast']);
});
