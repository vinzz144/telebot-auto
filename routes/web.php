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

Route::get('/', function () {
    return view('welcome');
});


Route::get('bot/get-me','BotController@get_me');
Route::get('bot/get-updates','BotController@get_updates');
Route::get('bot/reply-message','BotController@reply_message');
Route::get('bot/auto-responder','BotController@auto_responder');
