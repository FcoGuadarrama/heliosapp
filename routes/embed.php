<?php

use App\Http\Controllers\EmbedIntakeController;
use Illuminate\Support\Facades\Route;

// Deliberately stateless. No web session, cookies or ambient CRM authentication.
Route::group([], function (): void {
    Route::get('/embed/cfe', [EmbedIntakeController::class, 'show'])->name('embed.cfe');
    Route::post('/embed/cfe', [EmbedIntakeController::class, 'store'])->name('embed.cfe.store');
});
