<?php

use App\Http\Controllers\W3xController;
use Illuminate\Support\Facades\Route;

Route::get('/', [W3xController::class, 'index']);
