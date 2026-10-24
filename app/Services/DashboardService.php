<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(private LedgerService $ledger) {}

    public function summary(int $businessId): array
    {
        $parties = Party::where('business_id', $businessId)->whereIn('type', ['customer', 'supplier'])->get();

        $youWillGet = 0.0;
        $youWillGive = 0.0;
        foreach ($parties as $party) {
            $bal = $this->ledger->partyBalance($party);
            if ($bal > 0) {
                $youWillGet += $bal;
            } elseif ($bal < 0) {
                $youWillGive += abs($bal);
            }
        }

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales = (float) Sale::where('business_id', $businessId)->whereDate('date', $today)->sum('grand_total');
        $monthlySales = (float) Sale::where('business_id', $businessId)->whereDate('date', '>=', $monthStart)->sum('grand_total');
        $todayExpenses = (float) Expense::where('business_id', $businessId)->whereDate('date', $today)->sum('amount');
        $monthlyExpenses = (float) Expense::where('business_id', $businessId)->whereDate('date', '>=', $monthStart)->sum('amount');

        $cashInHand = (float) Transaction::where('business_id', $businessId)
            ->whereIn('type', ['receive', 'income', 'payment'])
            ->sum('amount')
            - (float) Transaction::where('business_id', $businessId)
                ->whereIn('type', ['give', 'expense', 'purchase'])
                ->sum('amount');

        $lowStock = Product::where('business_id', $businessId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        return [
            'you_will_get' => round($youWillGet, 2),
            'you_will_give' => round($youWillGive, 2),
            'cash_in_hand' => round($cashInHand, 2),
            'today_sales' => round($todaySales, 2),
            'monthly_sales' => round($monthlySales, 2),
            'today_expenses' => round($todayExpenses, 2),
            'monthly_expenses' => round($monthlyExpenses, 2),
            'total_customers' => Party::where('business_id', $businessId)->where('type', 'customer')->count(),
            'total_suppliers' => Party::where('business_id', $businessId)->where('type', 'supplier')->count(),
            'total_products' => Product::where('business_id', $businessId)->count(),
            'low_stock_products' => $lowStock,
            'outstanding_receivables' => round($youWillGet, 2),
            'outstanding_payables' => round($youWillGive, 2),
            'recent_transactions' => Transaction::where('business_id', $businessId)->latest('date')->limit(10)->get(),
            'recent_invoices' => Invoice::where('business_id', $businessId)->latest('invoice_date')->limit(5)->get(),
            'recent_expenses' => Expense::where('business_id', $businessId)->latest('date')->limit(5)->get(),
        ];
    }
}
