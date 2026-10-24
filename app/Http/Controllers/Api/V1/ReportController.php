<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Services\DashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ResolvesBusiness;

    public function __construct(private DashboardService $dashboard) {}

    public function profitLoss(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $from = $request->query('from_date');
        $to = $request->query('to_date');
        $salesQ = Sale::where('business_id', $businessId);
        $expQ = Expense::where('business_id', $businessId);
        if ($from) {
            $salesQ->whereDate('date', '>=', $from);
            $expQ->whereDate('date', '>=', $from);
        }
        if ($to) {
            $salesQ->whereDate('date', '<=', $to);
            $expQ->whereDate('date', '<=', $to);
        }
        $revenue = (float) $salesQ->sum('grand_total');
        $expenses = (float) $expQ->sum('amount');

        return ApiResponse::success([
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_profit' => $revenue - $expenses,
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Sale::where('business_id', $this->businessId($request))->latest('date')->limit(100)->get()
        );
    }

    public function purchases(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Purchase::where('business_id', $this->businessId($request))->latest('date')->limit(100)->get()
        );
    }

    public function expenses(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Expense::where('business_id', $this->businessId($request))->latest('date')->limit(100)->get()
        );
    }

    public function cashFlow(Request $request): JsonResponse
    {
        return ApiResponse::success($this->dashboard->summary($this->businessId($request)));
    }

    public function stock(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Product::where('business_id', $this->businessId($request))->get()
        );
    }

    public function receivables(Request $request): JsonResponse
    {
        $summary = $this->dashboard->summary($this->businessId($request));

        return ApiResponse::success(['outstanding_receivables' => $summary['outstanding_receivables']]);
    }

    public function payables(Request $request): JsonResponse
    {
        $summary = $this->dashboard->summary($this->businessId($request));

        return ApiResponse::success(['outstanding_payables' => $summary['outstanding_payables']]);
    }
}
