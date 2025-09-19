<?php

use App\Jobs\TestJob;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/i', function () {
    phpinfo();
});

Route::get('/test-assynch', function () {
    TestJob::dispatch(5);
    TestJob::dispatch(6);
    TestJob::dispatch(7);
});
