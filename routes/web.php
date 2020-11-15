<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//markers
Route::get('/get', 'DashboardController@getMarker');
Route::get('/get', [App\Http\Controllers\DashboardController::class, 'getMarker']);

Route::get('/set', 'DashboardController@setMarker');
Route::get('/set', [App\Http\Controllers\DashboardController::class, 'setMarker']);

//index

Route::view('/test', 'test');

Route::get('/{para?}', [App\Http\Controllers\DashboardController::class,'index'])->name('dashboard');

//upload
Route::post('/{para?}', [App\Http\Controllers\DashboardController::class, 'upload'])->name('upload-song');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
