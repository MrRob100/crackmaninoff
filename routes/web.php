<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/file/storage', function() {
    $files = \Illuminate\Support\Facades\Storage::disk('s3')->files(env('BUCKET_DIR')); //works
//    dd($files);

    $url = \Illuminate\Support\Facades\Storage::disk('s3')->url('capricosa.mp3');

//    dd($url);


//    $file = $file->storePubliclyAs($path, $filename);



});


//download
Route::get('/download', [DashboardController::class, 'download']);

//delete
Route::get('/delete', [DashboardController::class, 'delete']);

Auth::routes();

Route::get('/{para?}', [DashboardController::class,'index'])->name('dashboard');

//upload
Route::post('/{para?}', [DashboardController::class, 'upload'])->name('upload-song');
