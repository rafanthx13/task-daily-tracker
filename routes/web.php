<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

use App\Http\Controllers\TagController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\TaskAnalyticsController;
use App\Http\Controllers\NoteController;


Route::get('/', [TaskController::class, 'index'])->name('home');
Route::get('/day/{date}', [TaskController::class, 'index'])->name('tasks.day');

// Renova a sessão e fornece um token válido para páginas mantidas abertas.
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');

Route::get('/previous-day-tasks', [TaskController::class, 'previousDayTasks'])->name('previousDayTasks');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

Route::put('/tasks/change-lane/{task}', [TaskController::class, 'updateLane'])->name('tasks.change-lane');
Route::put('/tasks/{id}', [TaskController::class, 'update']);
Route::get('/tasks/view/{id}', [TaskController::class, 'show'])->name('tasks.show');
Route::delete('/tasks/{id}', [TaskController::class, 'delete']);

Route::get('/get-tasks-from-old-date/{oldDate}/{todayDate}', [TaskController::class, 'copyTasksFromDate'])->name('tasks.copyTasksFromDate');

Route::resource('tags', TagController::class);
Route::resource('achievements', AchievementController::class);
Route::resource('notes', NoteController::class);

Route::get('/analytics', [TaskAnalyticsController::class, 'indexView'])->name('analytics.index');
Route::get('/api/analytics/month', [TaskAnalyticsController::class, 'monthReportData'])->name('api.analytics.month');

use App\Http\Controllers\ReminderController;

Route::prefix('reminders')->name('reminders.')->group(function () {
    Route::get('/', [ReminderController::class, 'index'])->name('index');
    Route::get('/sporadic', [ReminderController::class, 'sporadicIndex'])->name('sporadic.index');
    Route::get('/recurring', [ReminderController::class, 'recurringIndex'])->name('recurring.index');
    Route::get('/finished', [ReminderController::class, 'finishedIndex'])->name('finished');
    Route::post('/', [ReminderController::class, 'store'])->name('store');
    Route::put('/{id}', [ReminderController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReminderController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/complete', [ReminderController::class, 'complete'])->name('complete');
    Route::post('/{id}/finish-sporadic', [ReminderController::class, 'finishSporadic'])->name('finish-sporadic');
});

Route::post('/day-summary/preview', [\App\Http\Controllers\DaySummaryController::class, 'preview'])->name('day-summary.preview');
Route::post('/day-summary', [\App\Http\Controllers\DaySummaryController::class, 'storeOrUpdate'])->name('day-summary.store');

use App\Http\Controllers\TimeManagementController;

Route::prefix('time-management')->name('time-management.')->group(function () {
    Route::get('/entries/{date}', [TimeManagementController::class, 'getEntriesByDate'])->name('entries');
    Route::post('/sync', [TimeManagementController::class, 'syncEntries'])->name('sync');

    // Tag management
    Route::get('/tags', [TimeManagementController::class, 'indexTags'])->name('tags.index');
    Route::post('/tags', [TimeManagementController::class, 'storeTag'])->name('tags.store');
    Route::put('/tags/{tag}', [TimeManagementController::class, 'updateTag'])->name('tags.update');
    Route::delete('/tags/{tag}', [TimeManagementController::class, 'destroyTag'])->name('tags.destroy');
});
