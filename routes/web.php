<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/', function () {
        return view('welcome');
    })->middleware('guest');
    Route::get('/home', function(){
    	return view('welcome');
    });
    Route::get('/game', 'GameController@joinGame');
    Route::get('addCell', 'GameController@addCell');
    Route::get('updateTurn', 'GameController@updateTurn');
    Route::get('startGame', 'GameController@startGame');
    Route::get('loadGame', 'GameController@loadGame');
    Route::get('loadGrid','GameController@loadGrid');
    Route::post('leaveGame', 'GameController@leaveGame');
    Route::post('ChangeHost', 'GameController@ChangeHost');
    Route::auth();
});
