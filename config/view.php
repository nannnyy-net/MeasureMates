<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | This option controls the location where cached views will be stored.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env('VIEW_COMPILED_PATH', storage_path('framework/views')),

];

