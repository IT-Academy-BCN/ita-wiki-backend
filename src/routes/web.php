<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'ITA Wiki API running',
    ]);
});