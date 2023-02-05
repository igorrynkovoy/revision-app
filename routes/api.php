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
Route::group(['prefix' => 'workspaces', 'namespace' => 'Workspace'], function () {
    Route::get('/list', 'WorkspaceController@getList');
    Route::post('/create', 'WorkspaceController@postCreate');
    Route::post('/edit/{workspace}', 'WorkspaceController@postEdit');

    Route::get('/{workspace}/details', 'WorkspaceController@getDetails');
    Route::group(['prefix' => '/{workspace}/labels'], function () {
        Route::get('/list', 'LabelController@getList');
        Route::post('/create', 'LabelController@postCreate');
        Route::post('/edit/{addressLabelID}', 'LabelController@postEdit');
        Route::post('/delete/{addressLabelID}', 'LabelController@postDelete');
        Route::post('/import-csv', 'LabelController@postImportCSV');
    });
});

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
        Route::group(['prefix' => 'transaction'], function () {
            Route::get('/list', 'TransactionController@getList');
            Route::get('/{txhash}', 'TransactionController@getTransaction');
        });
    });
});
