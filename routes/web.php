<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\BookController;

// Enables RESTful routing for books.
// This line maps the route name to the corresponding method in BookController.
Route::resource('books', BookController::class);
