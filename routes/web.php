<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\taskController;

use App\Http\Controllers\TagController;

Route::get('/', [taskController::class, 'index'])->name('home');
Route::get('/day/{date}', [taskController::class, 'index'])->name('tasks.day');

Route::get('/previous-day-tasks', [taskController::class, 'previousDayTasks'])->name('previousDayTasks');
Route::post('/tasks', [taskController::class, 'store'])->name('tasks.store');

Route::put('/tasks/change-lane/{task}', [taskController::class, 'updateLane'])->name('tasks.change-lane');
Route::put('/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/tasks/{id}', [TaskController::class, 'delete']);

Route::get('/get-tasks-from-old-date/{oldDate}/{todayDate}', [taskController::class, 'getOldDate'])->name('tasks.getOldDate');

Route::resource('tags', TagController::class);
