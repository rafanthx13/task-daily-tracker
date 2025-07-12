<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\taskController;

use App\Http\Controllers\TagController;

Route::get('/', [taskController::class, 'index'])->name('home');

Route::post('/tasks', [taskController::class, 'store'])->name('tasks.store');

Route::resource('tags', TagController::class);
