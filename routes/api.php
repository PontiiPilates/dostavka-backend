<?php

use App\Http\Controllers\Api\AutocompleteController;
use App\Http\Controllers\Api\CalculateController;
use App\Http\Controllers\Api\RedisController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/autocomplete', [AutocompleteController::class, 'handle'])->name('autocomplete');

    Route::get('/calculate', [CalculateController::class, 'handle'])->name('calculate');

    Route::get('/calculate-result', [RedisController::class, 'handle'])->name('calculate-result');
    Route::get('/view-all', [RedisController::class, 'viewAll'])->name('view-all');
    Route::get('/clear', [RedisController::class, 'clear'])->name('clear');
});
