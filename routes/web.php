<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        "Laravel" => "v" . \Illuminate\Foundation\Application::VERSION,
        "PHP" => "v" . phpversion(),
    ]);
});
