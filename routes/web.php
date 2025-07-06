<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\taskController;

Route::get('/', [taskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [taskController::class, 'store'])->name('tasks.store');
