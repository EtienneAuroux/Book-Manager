<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

// Enables RESTful routing for books.
// This line maps the route name to the corresponding method in BookController.
Route::resource('books', BookController::class);

// Route for export.
// {type} can be: all, titles or authors
// {format} can be: csv or xml
Route::get('/books/export/{type}/{format}', [BookController::class, 'export'])->name('books.export');
