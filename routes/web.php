<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\WarehouseInventoryController;
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

    // Shipments & containers: accessible to admin and operation_manager
    Route::resource('shipments', ShipmentController::class)
        ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,operation_manager');

    Route::resource('containers', ContainerController::class)
        ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,operation_manager');

    // Warehouse inventory: admin and warehouse_staff
    Route::resource('warehouse-inventory', WarehouseInventoryController::class)
        ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,warehouse_staff');

    // Payments and supplier settlements: admin and finance
    Route::resource('payments', App\Http\Controllers\PaymentController::class)
        ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,finance');

    Route::resource('supplier-settlements', App\Http\Controllers\SupplierSettlementController::class)
        ->middleware(\App\Http\Middleware\EnsureRole::class . ':admin,finance');
    Route::resource('receipts', App\Http\Controllers\ReceiptController::class)->only(['index','create','store','show']);
    // Admin area: admin role only
    Route::middleware(\App\Http\Middleware\EnsureRole::class . ':admin')->group(function () {
        Route::get('admin/staff', [App\Http\Controllers\StaffController::class, 'index'])->name('admin.staff.index');
        Route::get('admin/staff/{id}', [App\Http\Controllers\StaffController::class, 'show'])->name('admin.staff.show');
        Route::post('admin/staff/{id}/roles', [App\Http\Controllers\StaffController::class, 'updateRoles'])->name('admin.staff.roles.update');
        Route::get('admin/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('admin/roles', [App\Http\Controllers\RoleController::class, 'store'])->name('admin.roles.store');
        Route::delete('admin/roles/{id}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('admin.roles.destroy');
        // Audit logs
        Route::get('admin/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('admin.audit.index');
        Route::get('admin/audit-logs/{id}', [App\Http\Controllers\AuditLogController::class, 'show'])->name('admin.audit.show');
        Route::post('audit-logs', [App\Http\Controllers\AuditLogController::class, 'store'])->name('audit.store');
    });
});

require __DIR__.'/settings.php';
