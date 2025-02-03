<?php

use App\Http\Controllers\IndexPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexPage::class, 'index']);
Route::post('/update-copy-status', [App\Http\Controllers\FileController::class, 'updateCopyStatus']);
Route::post('/update-copy-child', [App\Http\Controllers\FileController::class, 'updateCopyChild']);
Route::get('/commit', [App\Http\Controllers\FileController::class, 'updateValidateStatus']);
Route::get('/copy-child', [App\Http\Controllers\FileController::class, 'copyChild']);
Route::get('/set-build', [App\Http\Controllers\FileController::class, 'setBuild']);
Route::post('/switch-project', [App\Http\Controllers\FileController::class, 'switchProject']);
