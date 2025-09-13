<?php

use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\AuthController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// require_once 'auth.php';

Route::prefix('v1')->group(function () {

    // калькуляция
    Route::get('/autocomplete', [AutocompleteController::class, 'handle'])->name('autocomplete');
    Route::get('/calculate', [CalculateController::class, 'handle'])->name('calculate');
    Route::get('/calculate-result', [RedisController::class, 'handle'])->name('calculate-result');

    // вспомогательные маршруты разработки
    Route::get('/view-all', [RedisController::class, 'viewAll'])->name('view-all');
    Route::get('/clear', [RedisController::class, 'clear'])->name('clear');

    // личный кабинет
    Route::post('register', [AuthController::class, 'register'])->name('register')->middleware('auth:sanctum');
    Route::post('login', [AuthController::class, 'login'])->name('login')->middleware('auth:sanctum');

    Route::get('me', [AuthController::class, 'me'])->name('me')->middleware('auth:sanctum');

    Route::get('verification-email', [AuthController::class, 'verificationNotice'])->name('verification.notice')->middleware('auth:sanctum', 'throttle:5,1'); // done
    Route::get('verification-email/{id}/{hash}', [AuthController::class, 'verificationVerify'])->name('verification.verify')->middleware('auth:sanctum', 'signed', 'throttle:5,1'); // done

    Route::post('password-forgot', [AuthController::class, 'passwordForgot'])->name('password.forgot')->middleware('guest'); // doneS
    Route::post('password-reset', [AuthController::class, 'passwordReset'])->name('password.reset')->middleware('guest'); // done

    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

    Route::delete('delete', [AuthController::class, 'delete'])->name('delete')->middleware('auth:sanctum');

    // работа с токенами доступа
    Route::post('api-token-create', [AuthController::class, 'apiTokenCreate'])->name('api.token.create')->middleware('auth:sanctum', 'throttle:60,1');
    Route::post('api-token-remove', [AuthController::class, 'apiTokenRemove'])->name('api.token.remove')->middleware('auth:sanctum');
});


// todo: middleware auth:sanctum не должен перенаправлять, должен отдавать ответ.Ema