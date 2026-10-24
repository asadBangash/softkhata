<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        $q = Product::where('business_id', $this->businessId($request));
        if ($search = $request->query('search')) {
            $q->where('name', 'like', "%{$search}%");
        }
        if ($request->boolean('low_stock')) {
            $q->whereColumn('current_stock', '<=', 'minimum_stock');
        }
        if ($request->boolean('out_of_stock')) {
            $q->where('current_stock', '<=', 0);
        }

        return ApiResponse::paginated($q->paginate((int) $request->query('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer'],
            'unit_id' => ['nullable', 'integer'],
            'name' => ['required', 'string'],
            'sku' => ['nullable', 'string'],
            'barcode' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'current_stock' => ['nullable', 'numeric'],
            'minimum_stock' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $product = Product::create(array_merge($data, ['business_id' => $this->businessId($request)]));

        return ApiResponse::success($product, 'Product created', 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $this->assertBusinessModel($request, $product);

        return ApiResponse::success($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->assertBusinessModel($request, $product);
        $product->update($request->validate([
            'name' => ['sometimes', 'string'],
            'sku' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'minimum_stock' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($product, 'Product updated');
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->assertBusinessModel($request, $product);
        $product->delete();

        return ApiResponse::success(null, 'Product deleted');
    }

    public function stockHistory(Request $request, Product $product): JsonResponse
    {
        $this->assertBusinessModel($request, $product);
        $history = StockMovement::where('product_id', $product->id)->latest()->paginate(20);

        return ApiResponse::paginated($history);
    }

    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $this->assertBusinessModel($request, $product);
        $data = $request->validate([
            'type' => ['required', 'in:adjustment_in,adjustment_out,damage'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $product, $data) {
            $qty = (float) $data['quantity'];
            if (in_array($data['type'], ['adjustment_out', 'damage'], true)) {
                $qty = -abs($qty);
            } else {
                $qty = abs($qty);
            }
            $product->current_stock = (float) $product->current_stock + $qty;
            $product->save();

            StockMovement::create([
                'business_id' => $product->business_id,
                'product_id' => $product->id,
                'type' => $data['type'],
                'quantity' => abs((float) $data['quantity']),
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        });

        return ApiResponse::success($product->fresh(), 'Stock adjusted');
    }
}
