<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashBookController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $q = CashTransaction::where('business_id', $businessId);
        if ($from = $request->query('from_date')) {
            $q->whereDate('date', '>=', $from);
        }
        if ($to = $request->query('to_date')) {
            $q->whereDate('date', '<=', $to);
        }

        $cashIn = (clone $q)->where('direction', 'in')->sum('amount');
        $cashOut = (clone $q)->where('direction', 'out')->sum('amount');
        $opening = (float) CashAccount::where('business_id', $businessId)->value('opening_balance');

        return ApiResponse::success([
            'opening_cash' => $opening,
            'cash_in' => (float) $cashIn,
            'cash_out' => (float) $cashOut,
            'closing_cash' => $opening + (float) $cashIn - (float) $cashOut,
            'transactions' => $q->latest('date')->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);
        $businessId = $this->businessId($request);
        $account = CashAccount::firstOrCreate(
            ['business_id' => $businessId],
            ['name' => 'Main Cash', 'opening_balance' => 0]
        );

        $row = CashTransaction::create(array_merge($data, [
            'business_id' => $businessId,
            'cash_account_id' => $account->id,
            'created_by' => $request->user()->id,
        ]));

        return ApiResponse::success($row, 'Cash entry recorded', 201);
    }

    public function show(Request $request, CashTransaction $cashTransaction): JsonResponse
    {
        $this->assertBusinessModel($request, $cashTransaction);

        return ApiResponse::success($cashTransaction);
    }

    public function update(Request $request, CashTransaction $cashTransaction): JsonResponse
    {
        $this->assertBusinessModel($request, $cashTransaction);
        $cashTransaction->update($request->validate([
            'amount' => ['sometimes', 'numeric'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($cashTransaction, 'Updated');
    }

    public function destroy(Request $request, CashTransaction $cashTransaction): JsonResponse
    {
        $this->assertBusinessModel($request, $cashTransaction);
        $cashTransaction->delete();

        return ApiResponse::success(null, 'Deleted');
    }
}
