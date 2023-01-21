<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routesd
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/bootstrap', ['uses' => 'BootstrapController@getBootstrap']);
Route::group(['prefix' => 'blockchain', 'namespace' => 'Blockchain'], function () {
    Route::group(['prefix' => 'depth-sync'], function () {
        Route::get('/list', 'DepthSyncController@getList');
        Route::get('/details/{rootDepthSync}/{depth?}', 'DepthSyncController@getDepthSync');
        Route::post('/create', 'DepthSyncController@postCreate');
        Route::post('/delete/{rootDepthSync}', 'DepthSyncController@postDelete');
    });

    Route::group(['prefix' => 'litecoin', 'namespace' => 'Litecoin'], function () {
        Route::group(['prefix' => 'address'], function () {
            Route::get('/list', 'AddressController@getList');
        });
    });
});