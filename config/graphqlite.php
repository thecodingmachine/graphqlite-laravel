<?php

use GraphQL\Error\DebugFlag;
use TheCodingMachine\GraphQLite\Http\HttpCodeDecider;

return [
    /*
     |--------------------------------------------------------------------------
     | GraphQLite Configuration
     |--------------------------------------------------------------------------
     |
     | Use this configuration to customize the namespace of the controllers and
     | types.
     | These namespaces must be autoloadable from Composer.
     | GraphQLite will find the path of the files based on composer.json settings.
     |
     | You can put a single namespace, or an array of namespaces.
     |
     */
    'controllers' => 'App\\Http\\Controllers',
    'types' => 'App\\',
    'debug' => DebugFlag::RETHROW_UNSAFE_EXCEPTIONS,
    'uri' => env('GRAPHQLITE_URI', '/graphql'),
    'middleware' =>  ['web'],

    // Sets the status code in the HTTP request where operations have errors.
    'decider' => HttpCodeDecider::class,
];
