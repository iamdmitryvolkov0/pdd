<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tickets', [SessionController::class, 'tickets'])->name('tickets');
Route::get('/questions', [SessionController::class, 'questions'])->name('questions');
Route::post('/sessions', [SessionController::class, 'store'])->name('sessions.store');
Route::get('/sessions/{practiceSession}', [SessionController::class, 'show'])->name('sessions.show');
Route::post('/sessions/{practiceSession}/answers', [SessionController::class, 'answer'])->name('sessions.answer');
Route::post('/sessions/{practiceSession}/finish', [SessionController::class, 'finish'])->name('sessions.finish');
Route::get('/statistics', StatisticsController::class)->name('statistics');
Route::delete('/statistics', [StatisticsController::class, 'destroy'])->name('statistics.destroy');
