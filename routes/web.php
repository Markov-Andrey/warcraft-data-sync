<?php

use Illuminate\Support\Facades\Route;

// SPA — все маршруты отдают единую оболочку, роутинг на стороне Vue
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
