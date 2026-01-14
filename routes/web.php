<?php

use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('lang/{locale}', function (string $locale) {
    $supported = config('app.supported_locales', ['en', 'ar']);

    if (! in_array($locale, $supported, true)) {
        $locale = config('app.locale');
    }

    session(['locale' => $locale]);

    return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
})->name('lang.switch');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('products/{product}/images', [ProductController::class, 'uploadImages'])->name('products.images.store');
        Route::delete('products/{product}/images/{media}', [ProductController::class, 'deleteImage'])->name('products.images.destroy');
        Route::put('products/{product}/images/reorder', [ProductController::class, 'reorderImages'])->name('products.images.reorder');
        Route::put('products/{product}/images/{media}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');

        Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
        Route::get('colors/create', [ColorController::class, 'create'])->name('colors.create');
        Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
        Route::get('colors/{color}/edit', [ColorController::class, 'edit'])->name('colors.edit');
        Route::put('colors/{color}', [ColorController::class, 'update'])->name('colors.update');
        Route::delete('colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');

        Route::view('categories', 'admin.categories.index')->name('categories.index');
        Route::view('categories/create', 'admin.categories.create')->name('categories.create');
        Route::view('categories/{category}/edit', 'admin.categories.edit')->name('categories.edit');

        Route::view('orders', 'admin.orders.index')->name('orders.index');

        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/export/{type}/{format}', [ReportsController::class, 'export'])->name('reports.export');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
        Route::post('settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo');
        Route::post('settings/payments/{provider}', [PaymentSettingsController::class, 'update'])->name('settings.payments');
    });

Route::middleware(['auth', 'verified'])
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::view('orders/{order}', 'admin.orders.index')->name('orders.show');
    });

require __DIR__.'/settings.php';
