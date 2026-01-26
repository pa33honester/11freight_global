<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ContainerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('shipments', ShipmentController::class);
    Route::resource('containers', ContainerController::class);
});

require __DIR__.'/settings.php';
