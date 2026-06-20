<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('kanban');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});


use App\Http\Controllers\KanBanBoard;
use App\Http\Controllers\Leads;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('kanban', [KanBanBoard::class, 'index'])->name('kanban');
    Route::get('kanban/data', [KanBanBoard::class, 'getLeads'])->name('kanban.data');
    Route::post('kanban/update', [KanBanBoard::class, 'update'])->name('kanban.update');
    Route::delete('kanban/delete/{lead}', [KanBanBoard::class, 'destroy'])->name('kanban.destroy');

    Route::get('leads', [Leads::class, 'index'])->name('leads');
    Route::post('leads/batch', [Leads::class, 'batchStore'])->name('leads.batch');
    Route::inertia('integrations', 'integrations/Integrations')->name('integrations');
});

use App\Http\Controllers\WebhookController;

Route::post('v1/webhooks/twilio', [WebhookController::class, 'twilio']);
Route::post('v1/webhooks/google-form', [WebhookController::class, 'googleForm']);
Route::match(['get', 'post'], 'v1/webhooks/facebook', [WebhookController::class, 'facebook']);
Route::match(['get', 'post'], 'v1/webhooks/instagram', [WebhookController::class, 'instagram']);

require __DIR__ . '/settings.php';

