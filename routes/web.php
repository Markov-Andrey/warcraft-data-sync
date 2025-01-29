<?php

use App\Http\Controllers\W3xController;
use Illuminate\Support\Facades\Route;

Route::get('/', [W3xController::class, 'index']);
Route::post('/update-copy-status', [App\Http\Controllers\FileController::class, 'updateCopyStatus']);
Route::get('/commit', [App\Http\Controllers\FileController::class, 'updateValidateStatus']);
