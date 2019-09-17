<?php

Route::group(['middleware' => config('graphqlite.middleware')], function () {
    $uri = config('graphqlite.uri', '/graphql');

    Route::get($uri, 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
    Route::post($uri, 'TheCodingMachine\\GraphQLite\\Laravel\\Controllers\\GraphQLiteController@index');
});