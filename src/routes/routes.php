<?php

Route::get(config('graphqlite.uri'), 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
Route::post(config('graphqlite.uri'), 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
