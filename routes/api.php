<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\IndexPage;
use App\Http\Controllers\JsonController;
use App\Services\CrudJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/project-info', [IndexPage::class, 'projectInfo']);

Route::get('/copy-child', [FileController::class, 'copyChild']);
Route::get('/set-build', [FileController::class, 'setBuild']);
Route::post('/switch-project', [FileController::class, 'switchProject']);
Route::post('/update-version', [FileController::class, 'setVersion']);

Route::get('/parse-map', [JsonController::class, 'parseMap']);
Route::get('/units', [JsonController::class, 'units']);
Route::post('/update', function (Request $request) {
    return CrudJson::updateValue(
        $request->input('db'),
        $request->input('id'),
        $request->input('key'),
        $request->input('value'),
    );
});
