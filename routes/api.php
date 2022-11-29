<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'explorer',
    'middleware' => 'auth.api'
], function () {
    Route::apiResource('file', 'FileController');
    Route::apiResource('search', 'FileSearchController')->only('index');
    foreach (['favorites', 'move', 'copy', 'rename'] as $route) {
        Route::apiResource($route, ucfirst($route) . 'Controller')->only('update');
    }
});
