<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PreferredItemController;
use App\Http\Controllers\Admin\StockController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->group(function(){
    Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('',[DashboardController::class,'Index']);
    Route::get('notification',[NotificationController::class,'markAsRead'])->name('mark-as-read');
    Route::get('notification-read',[NotificationController::class,'read'])->name('read');
    Route::get('notifications/unread',[NotificationController::class,'unread'])->name('notifications.unread');
    Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');
    Route::post('logout',[LogoutController::class,'index'])->name('logout');

    Route::resource('users',UserController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','destroy']);
    Route::put('permission',[PermissionController::class,'update'])->name('permissions.update');
    Route::resource('roles',RoleController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class)->only(['index','store','destroy']);
    Route::put('categories',[CategoryController::class,'update'])->name('categories.update');
    Route::resource('purchases',PurchaseController::class)->except('show');
    // Edit without id (AJAX-driven editor) and JSON endpoint for purchases
    Route::get('purchases/edit',[PurchaseController::class,'editNoId'])->name('purchases.edit.noid');
    Route::get('purchases/{purchase}/json',[PurchaseController::class,'json'])->name('purchases.json');
    Route::get('purchases/reports',[PurchaseController::class,'reports'])->name('purchases.report');
    Route::post('purchases/reports',[PurchaseController::class,'generateReport']);
    Route::resource('products',ProductController::class)->except('show');
    Route::get('products/barcode/{query}',[ProductController::class,'barcodeLookup'])->name('products.barcode');
    Route::get('products/outstock',[ProductController::class,'outstock'])->name('outstock');
    Route::get('products/expired',[ProductController::class,'expired'])->name('expired');
    // Stock summary
    Route::get('stock/summary',[StockController::class,'summary'])->name('stock.summary');
    Route::resource('sales',SaleController::class)->except('show');
    // POS-style sale endpoint (accepts multiple items)
    Route::post('sales/pos',[SaleController::class,'posStore'])->name('sales.pos');
    // Delete entire invoice (grouped by timestamp of the passed sale ID)
    Route::delete('sales/invoice/{id}',[SaleController::class,'destroyInvoice'])->name('sales.destroy.invoice');
    Route::get('sales/invoice',[SaleController::class,'showInvoice'])->name('sales.invoice');
    Route::get('sales/todays-for-print',[SaleController::class,'todaysSalesForPrint'])->name('sales.todays-for-print');
    Route::get('sales/todays-print',[SaleController::class,'todaysSalesPrint'])->name('sales.todays-print');
    Route::get('sales/invoice_print',[SaleController::class,'invoicePrintLatest'])->name('sales.invoice_print');
    Route::get('sales/reports',[SaleController::class,'reports'])->name('sales.report');
    Route::post('sales/reports',[SaleController::class,'generateReport']);

    // Preferred Items Routes
    Route::get('preferred-items',[PreferredItemController::class,'index'])->name('preferred-items.index');
    Route::post('preferred-items',[PreferredItemController::class,'store'])->name('preferred-items.store');
    Route::delete('preferred-items/{productId}',[PreferredItemController::class,'destroy'])->name('preferred-items.destroy');

    Route::get('backup', [BackupController::class,'index'])->name('backup.index');
    Route::put('backup/create', [BackupController::class,'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}', [BackupController::class,'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}', [BackupController::class,'destroy'])->where('file_name', '(.*)')->name('backup.destroy');

    // Save default printer via AJAX
    Route::post('settings/default-printer',[SettingController::class,'saveDefaultPrinter'])->name('settings.default-printer');
    
    // Save POS and general settings
    Route::post('settings/save',[SettingController::class,'savePOSSettings'])->name('settings.save');

    Route::get('settings',[SettingController::class,'index'])->name('settings');
});

Route::middleware(['guest'])->group(function () {
    Route::get('',function(){
        return redirect()->route('dashboard');
    });

    Route::get('login',[LoginController::class,'index'])->name('login');
    Route::post('login',[LoginController::class,'login']);

    Route::get('register',[RegisterController::class,'index'])->name('register');
    Route::post('register',[RegisterController::class,'store']);

    Route::get('forgot-password',[ForgotPasswordController::class,'index'])->name('password.request');
    Route::post('forgot-password',[ForgotPasswordController::class,'requestEmail']);
    Route::get('reset-password/{token}',[ResetPasswordController::class,'index'])->name('password.reset');
    Route::post('reset-password',[ResetPasswordController::class,'resetPassword'])->name('password.update');
});
