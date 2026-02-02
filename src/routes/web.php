<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';