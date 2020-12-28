<?php

use App\Http\Controllers\DashboardController;
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

//download
Route::get('/download', [DashboardController::class, 'download']);

//delete
Route::get('/delete', [DashboardController::class, 'delete']);

//markers
Route::get('/get', 'DashboardController@getMarker');
Route::get('/get', [DashboardController::class, 'getMarker']);

Route::get('/set', 'DashboardController@setMarker');
Route::get('/set', [DashboardController::class, 'setMarker']);

//index
Route::view('/test', 'test');

Route::get('/running', function() {
    return view('running');
});


Route::get('/{para?}', [DashboardController::class,'index'])->name('dashboard');

//upload
Route::post('/{para?}', [DashboardController::class, 'upload'])->name('upload-song');

Auth::routes();
