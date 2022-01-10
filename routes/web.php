<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

//download
Route::get('/download', [DashboardController::class, 'download']);

//delete
Route::get('/delete', [DashboardController::class, 'delete']);

Route::get('/{para?}', [DashboardController::class,'index'])->name('dashboard');

//upload
Route::post('/{para?}', [DashboardController::class, 'upload'])->name('upload-song');

Auth::routes();
