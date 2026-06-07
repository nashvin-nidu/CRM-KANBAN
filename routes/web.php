<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('kanban', 'kanban/Kanban')->name('kanban');
    Route::inertia('leads', 'leads/Leads')->name('leads');
});

require __DIR__ . '/settings.php';
