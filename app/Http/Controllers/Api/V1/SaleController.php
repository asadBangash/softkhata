<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            Sale::where('business_id', $this->businessId($request))->latest('date')->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'party_id' => ['nullable', 'exists:parties,id'],
            'date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric'],
            'tax' => ['nullable', 'numeric'],
            'amount_paid' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ]);

        $businessId = $this->businessId($request);

        $sale = DB::transaction(function () use ($request, $data, $businessId) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['rate'];
            }
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $grand = $subtotal - $discount + $tax;
            $paid = (float) ($data['amount_paid'] ?? 0);
            $balance = max($grand - $paid, 0);
            $status = $paid >= $grand ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $sale = Sale::create([
                'business_id' => $businessId,
                'party_id' => $data['party_id'] ?? null,
                'sale_number' => 'SALE-'.str_pad((string) (Sale::where('business_id', $businessId)->count() + 1), 6, '0', STR_PAD_LEFT),
                'date' => $data['date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grand,
                'amount_paid' => $paid,
                'balance' => $balance,
                'payment_status' => $status,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $amount = (float) $item['quantity'] * (float) $item['rate'];
                $sale->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'amount' => $amount,
                ]);

                if (! empty($item['product_id'])) {
                    $product = Product::where('business_id', $businessId)->findOrFail($item['product_id']);
                    $product->current_stock = (float) $product->current_stock - (float) $item['quantity'];
                    $product->save();
                    StockMovement::create([
                        'business_id' => $businessId,
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'quantity' => $item['quantity'],
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'created_by' => $request->user()->id,
                    ]);
                }
            }

            if (! empty($data['party_id'])) {
                Transaction::create([
                    'business_id' => $businessId,
                    'party_id' => $data['party_id'],
                    'type' => 'sale',
                    'amount' => $grand,
                    'date' => $data['date'],
                    'description' => 'Sale '.$sale->sale_number,
                    'created_by' => $request->user()->id,
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                ]);
                if ($paid > 0) {
                    Transaction::create([
                        'business_id' => $businessId,
                        'party_id' => $data['party_id'],
                        'type' => 'payment',
                        'amount' => $paid,
                        'date' => $data['date'],
                        'description' => 'Payment for '.$sale->sale_number,
                        'created_by' => $request->user()->id,
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                    ]);
                }
            }

            return $sale->load('items');
        });

        return ApiResponse::success($sale, 'Sale created', 201);
    }

    public function show(Request $request, Sale $sale): JsonResponse
    {
        $this->assertBusinessModel($request, $sale);

        return ApiResponse::success($sale->load('items'));
    }

    public function update(Request $request, Sale $sale): JsonResponse
    {
        $this->assertBusinessModel($request, $sale);
        $sale->update($request->validate([
            'notes' => ['nullable', 'string'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
        ]));

        return ApiResponse::success($sale, 'Sale updated');
    }

    public function destroy(Request $request, Sale $sale): JsonResponse
    {
        $this->assertBusinessModel($request, $sale);
        $sale->delete();

        return ApiResponse::success(null, 'Sale deleted');
    }
}
