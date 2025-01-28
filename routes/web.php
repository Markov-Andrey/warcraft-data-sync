<?php

use App\Http\Controllers\W3xController;
use Illuminate\Support\Facades\Route;

Route::get('/', [W3xController::class, 'index']);
Route::post('/update-config', [W3xController::class, 'updateConfig'])->name('updateConfig');
