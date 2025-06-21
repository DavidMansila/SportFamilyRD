<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/csrf-cookie', function (Request $request) {
    return response()->noContent();
});