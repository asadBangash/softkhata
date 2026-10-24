<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Transaction;
use App\Services\BusinessContext;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private BusinessContext $businessContext) {}

    public function index(Request $request): JsonResponse
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());
        $query = Transaction::where('business_id', $businessId)->with('party');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($from = $request->query('from_date')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = $request->query('to_date')) {
            $query->whereDate('date', '<=', $to);
        }

        return ApiResponse::paginated(
            $query->latest('date')->paginate((int) $request->query('per_page', 20))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());
        $data = $request->validate([
            'party_id' => ['nullable', 'exists:parties,id'],
            'type' => ['required', 'in:receive,give,sale,purchase,expense,income,payment,refund,adjustment'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($data['party_id'])) {
            $party = Party::findOrFail($data['party_id']);
            abort_if($party->business_id !== $businessId, 403);
        }

        $transaction = Transaction::create(array_merge($data, [
            'business_id' => $businessId,
            'created_by' => $request->user()->id,
        ]));

        return ApiResponse::success($transaction->load('party'), 'Transaction recorded', 201);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $this->assertAccess($request, $transaction);

        return ApiResponse::success($transaction->load('party'));
    }

    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $this->assertAccess($request, $transaction);
        $transaction->update($request->validate([
            'type' => ['sometimes', 'in:receive,give,sale,purchase,expense,income,payment,refund,adjustment'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'date' => ['sometimes', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($transaction, 'Transaction updated');
    }

    public function destroy(Request $request, Transaction $transaction): JsonResponse
    {
        $this->assertAccess($request, $transaction);
        $transaction->delete();

        return ApiResponse::success(null, 'Transaction deleted');
    }

    private function assertAccess(Request $request, Transaction $transaction): void
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());
        abort_if($transaction->business_id !== $businessId, 403);
    }
}
