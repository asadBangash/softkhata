<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            Purchase::where('business_id', $this->businessId($request))->latest('date')->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'party_id' => ['nullable', 'exists:parties,id'],
            'date' => ['required', 'date'],
            'grand_total' => ['required', 'numeric'],
            'amount_paid' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);
        $businessId = $this->businessId($request);
        $paid = (float) ($data['amount_paid'] ?? 0);
        $grand = (float) $data['grand_total'];
        $purchase = Purchase::create([
            'business_id' => $businessId,
            'party_id' => $data['party_id'] ?? null,
            'purchase_number' => 'PO-'.str_pad((string) (Purchase::where('business_id', $businessId)->count() + 1), 6, '0', STR_PAD_LEFT),
            'date' => $data['date'],
            'subtotal' => $grand,
            'grand_total' => $grand,
            'amount_paid' => $paid,
            'balance' => max($grand - $paid, 0),
            'payment_status' => $paid >= $grand ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return ApiResponse::success($purchase, 'Purchase created', 201);
    }

    public function show(Request $request, Purchase $purchase): JsonResponse
    {
        $this->assertBusinessModel($request, $purchase);

        return ApiResponse::success($purchase);
    }

    public function update(Request $request, Purchase $purchase): JsonResponse
    {
        $this->assertBusinessModel($request, $purchase);
        $purchase->update($request->only(['notes', 'payment_status']));

        return ApiResponse::success($purchase, 'Purchase updated');
    }

    public function destroy(Request $request, Purchase $purchase): JsonResponse
    {
        $this->assertBusinessModel($request, $purchase);
        $purchase->delete();

        return ApiResponse::success(null, 'Purchase deleted');
    }
}
