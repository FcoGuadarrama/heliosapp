<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentQuoteController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\InterconnectionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReceiptAnalysisController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('engineering/interconnection', [InterconnectionController::class, 'index'])->middleware('permission:receipts.import')->name('engineering.interconnection');
    Route::post('engineering/interconnection', [InterconnectionController::class, 'calculate'])->middleware(['permission:receipts.import', 'throttle:30,1'])->name('engineering.interconnection.calculate');

    Route::get('intakes/create', [IntakeController::class, 'create'])->middleware('permission:receipts.import')->name('intakes.create');
    Route::post('intakes', [IntakeController::class, 'store'])->middleware(['permission:receipts.import', 'throttle:10,1'])->name('intakes.store');
    Route::get('intakes/{intake}/file', [IntakeController::class, 'download'])->middleware('permission:receipts.view')->name('intakes.file');
    Route::post('intakes/{intake}/mock', [IntakeController::class, 'mock'])->middleware(['permission:receipts.import', 'throttle:10,1'])->name('intakes.mock');
    Route::get('intakes/{intake}/analysis', [ReceiptAnalysisController::class, 'show'])->middleware('permission:receipts.view')->name('intakes.analysis');
    Route::post('intakes/{intake}/analyze', [ReceiptAnalysisController::class, 'retry'])->middleware(['permission:receipts.import', 'throttle:10,1'])->name('intakes.analyze');
    Route::post('intakes/{intake}/review', [ReceiptAnalysisController::class, 'review'])->middleware(['permission:receipts.import', 'throttle:10,1'])->name('intakes.review');
    Route::post('intakes/{intake}/replacement', [ReceiptAnalysisController::class, 'replace'])->middleware(['permission:receipts.import', 'throttle:10,1'])->name('intakes.replacement');
    Route::get('intakes/{intake}/catalog', [EquipmentQuoteController::class, 'search'])->middleware(['permission:receipts.import', 'throttle:15,1'])->name('intakes.catalog');
    Route::post('intakes/{intake}/quote-drafts', [EquipmentQuoteController::class, 'store'])->middleware(['permission:receipts.import', 'throttle:5,1'])->name('intakes.quote-drafts');

    // Clients routes
    Route::get('clients', [ClientController::class, 'index'])
        ->middleware('permission:clients.view')
        ->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])
        ->middleware('permission:clients.create')
        ->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])
        ->middleware('permission:clients.create')
        ->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])
        ->middleware('permission:clients.view')
        ->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('permission:clients.update')
        ->name('clients.edit');
    Route::patch('clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:clients.update')
        ->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:clients.delete')
        ->name('clients.destroy');

    // Projects routes
    Route::get('projects', [ProjectController::class, 'index'])
        ->middleware('permission:projects.view')
        ->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])
        ->middleware('permission:projects.create')
        ->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])
        ->middleware('permission:projects.create')
        ->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->middleware('permission:projects.view')
        ->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])
        ->middleware('permission:projects.update')
        ->name('projects.edit');
    Route::patch('projects/{project}', [ProjectController::class, 'update'])
        ->middleware('permission:projects.update')
        ->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('permission:projects.delete')
        ->name('projects.destroy');
});

require __DIR__.'/settings.php';
