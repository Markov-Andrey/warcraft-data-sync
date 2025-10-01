<?php

use App\Http\Controllers\IndexPage;
use App\Http\Controllers\JsonController;
use App\Services\CrudJson;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexPage::class, 'index']);
Route::get('/copy-child', [App\Http\Controllers\FileController::class, 'copyChild']);
Route::get('/set-build', [App\Http\Controllers\FileController::class, 'setBuild']);
Route::post('/switch-project', [App\Http\Controllers\FileController::class, 'switchProject']);
Route::post('/update-version', [App\Http\Controllers\FileController::class, 'setVersion']);

Route::get('/units', [JsonController::class, 'units']);
Route::post('/update', function (Illuminate\Http\Request $request) {
    $db = $request->input('db');
    $id = $request->input('id');
    $key = $request->input('key');
    $value = $request->input('value');

    return CrudJson::updateValue($db, $id, $key, $value);
});
