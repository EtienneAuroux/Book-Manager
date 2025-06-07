<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\BookController;
// Enables RESTful routing for books
Route::resource('books', BookController::class);
