<?php

Route::group(['middleware' => config('graphqlite.middleware', ['web'])], function () {
    Route::get(config('graphqlite.uri', '/graphql'), 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
    Route::post(config('graphqlite.uri', '/graphql'), 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
});