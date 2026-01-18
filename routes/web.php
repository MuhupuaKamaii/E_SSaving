<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AdminDashboardController;

// 1. HOME PAGE
Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// 2. PROTECTED ROUTES (Must be logged in to see these)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // This is the main dashboard link used by the Sidebar
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Optional: Keep the /admin/dashboard link but protect it with auth too
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    // Add this inside your auth middleware group
Route::post('/transactions/{id}/approve', [AdminDashboardController::class, 'approveTransaction'])->name('transactions.approve');
});

// 3. SETTINGS & AUTH INTERNAL ROUTES
require __DIR__.'/settings.php';
