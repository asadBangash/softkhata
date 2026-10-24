<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\CashBookController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PartyController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\StaffController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'business.access'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        Route::get('dashboard', DashboardController::class);
        Route::get('payment-methods', [PaymentMethodController::class, 'index']);

        Route::apiResource('businesses', BusinessController::class);
        Route::post('businesses/{business}/switch', [BusinessController::class, 'switch']);

        Route::get('parties/{party}/ledger', [PartyController::class, 'ledger']);
        Route::apiResource('parties', PartyController::class);

        Route::apiResource('transactions', TransactionController::class);

        Route::get('cash-book', [CashBookController::class, 'index']);
        Route::post('cash-book', [CashBookController::class, 'store']);
        Route::get('cash-book/{cashTransaction}', [CashBookController::class, 'show']);
        Route::put('cash-book/{cashTransaction}', [CashBookController::class, 'update']);
        Route::delete('cash-book/{cashTransaction}', [CashBookController::class, 'destroy']);

        Route::get('products/{product}/stock-history', [ProductController::class, 'stockHistory']);
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
        Route::apiResource('products', ProductController::class);

        Route::apiResource('sales', SaleController::class);
        Route::apiResource('purchases', PurchaseController::class);

        Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment']);
        Route::apiResource('invoices', InvoiceController::class);

        Route::apiResource('expenses', ExpenseController::class);

        Route::get('staff/{staff}/attendance', [StaffController::class, 'attendanceIndex']);
        Route::post('staff/{staff}/attendance', [StaffController::class, 'attendanceStore']);
        Route::post('staff/{staff}/salary', [StaffController::class, 'salaryStore']);
        Route::post('staff/{staff}/advances', [StaffController::class, 'advanceStore']);
        Route::post('staff/{staff}/bonuses', [StaffController::class, 'bonusStore']);
        Route::post('staff/{staff}/overtime', [StaffController::class, 'overtimeStore']);
        Route::apiResource('staff', StaffController::class);

        Route::prefix('reports')->group(function () {
            Route::get('profit-loss', [ReportController::class, 'profitLoss']);
            Route::get('sales', [ReportController::class, 'sales']);
            Route::get('purchases', [ReportController::class, 'purchases']);
            Route::get('expenses', [ReportController::class, 'expenses']);
            Route::get('cash-flow', [ReportController::class, 'cashFlow']);
            Route::get('stock', [ReportController::class, 'stock']);
            Route::get('receivables', [ReportController::class, 'receivables']);
            Route::get('payables', [ReportController::class, 'payables']);
        });

        Route::put('notifications/read-all', [NotificationController::class, 'readAll']);
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);

        Route::get('settings', [SettingController::class, 'show']);
        Route::put('settings', [SettingController::class, 'update']);
    });
});
