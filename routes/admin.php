<?php

use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Catalogue
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    Route::get('produits', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('produits/creer', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('produits', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('produits/{product}/editer', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('produits/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('produits/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::post('produits/import', [AdminProductController::class, 'importCsv'])->name('products.import');
    Route::get('produits/export', [AdminProductController::class, 'exportCsv'])->name('products.export');

    Route::post('produits/{product}/variantes', [ProductVariantController::class, 'store'])->name('variants.store');
    Route::put('variantes/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('variantes/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');

    // Promotions
    Route::resource('coupons', CouponController::class)->except(['create', 'edit', 'show']);

    // Flash Sales
    Route::get('ventes-flash', [FlashSaleController::class, 'index'])->name('flash-sales.index');
    Route::post('ventes-flash', [FlashSaleController::class, 'store'])->name('flash-sales.store');
    Route::put('ventes-flash/{flashSale}', [FlashSaleController::class, 'update'])->name('flash-sales.update');
    Route::patch('ventes-flash/{flashSale}/toggle', [FlashSaleController::class, 'toggle'])->name('flash-sales.toggle');
    Route::delete('ventes-flash/{flashSale}', [FlashSaleController::class, 'destroy'])->name('flash-sales.destroy');

    // Commandes
    Route::get('commandes', [OrderController::class, 'index'])->name('orders.index');
    Route::get('commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('commandes/{order}/statut', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('commandes/{order}/remboursement', [OrderController::class, 'storeRefund'])->name('orders.refund');
    Route::get('commandes/{order}/facture', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('commandes/{order}/bon-preparation', [OrderController::class, 'preparationSlip'])->name('orders.preparation-slip');

    // Clients
    Route::get('clients', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('clients/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('clients/{customer}/bloquer', [CustomerController::class, 'toggleBlock'])->name('customers.toggle-block');
    Route::post('clients/{customer}/points', [CustomerController::class, 'adjustLoyaltyPoints'])->name('customers.loyalty-points');

    // Avis clients
    Route::get('avis', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('avis/{review}/approuver', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('avis/{review}/rejeter', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('avis/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Équipe
    Route::get('equipe', [StaffController::class, 'index'])->name('staff.index');
    Route::post('equipe', [StaffController::class, 'store'])->name('staff.store');
    Route::patch('equipe/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('equipe/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Comptabilité
    Route::get('comptabilite', [AccountingController::class, 'index'])->name('accounting.index');
    Route::get('comptabilite/export-pdf', [AccountingController::class, 'exportPdf'])->name('accounting.export-pdf');
    Route::get('comptabilite/export-csv', [AccountingController::class, 'exportCsv'])->name('accounting.export-csv');

    // Journal d'activité
    Route::get('journal', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Paramètres
    Route::get('parametres', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('parametres', [SettingsController::class, 'update'])->name('settings.update');
});
