<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Python Binary Path
    |--------------------------------------------------------------------------
    */
    'python_bin' => env('PYTHON_BINARY', 'python3'),

    /*
    |--------------------------------------------------------------------------
    | Python Scripts Directory
    |--------------------------------------------------------------------------
    | BUG FIX #5 — was base_path('python') which mismatched the controller.
    | The sanitize_metadata.py script lives at storage/app/py_scripts/.
    | Keep this in sync with where you actually place the .py file.
    */
    'python_scripts_path' => storage_path('app/py_scripts'),

    /*
    |--------------------------------------------------------------------------
    | Temp File Lifetime (minutes)
    |--------------------------------------------------------------------------
    */
    'temp_lifetime_minutes' => 5,
];