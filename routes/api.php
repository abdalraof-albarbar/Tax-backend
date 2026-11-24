<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get("/", function () {
    return DB::statement("INSERT INTO users (name, email, role, password) VALUES ('John Doe', 'john@xample.com', 'supervisor', 'password')");

});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
