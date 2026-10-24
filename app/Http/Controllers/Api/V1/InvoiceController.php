<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Transaction;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        $q = Invoice::where('business_id', $this->businessId($request));
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }

        return ApiResponse::paginated($q->latest('invoice_date')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'party_id' => ['required', 'exists:parties,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric'],
            'tax' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.rate' => ['required', 'numeric'],
        ]);

        $businessId = $this->businessId($request);
        $invoice = DB::transaction(function () use ($data, $businessId) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['rate'];
            }
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $grand = $subtotal - $discount + $tax;
            $number = 'INV-'.str_pad((string) (Invoice::where('business_id', $businessId)->count() + 1), 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'business_id' => $businessId,
                'party_id' => $data['party_id'],
                'invoice_number' => $number,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grand,
                'paid_amount' => 0,
                'balance_amount' => $grand,
                'status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'rate' => $item['rate'],
                    'amount' => (float) $item['quantity'] * (float) $item['rate'],
                ]);
            }

            Transaction::create([
                'business_id' => $businessId,
                'party_id' => $data['party_id'],
                'type' => 'sale',
                'amount' => $grand,
                'date' => $data['invoice_date'],
                'description' => 'Invoice '.$number,
                'source_type' => Invoice::class,
                'source_id' => $invoice->id,
            ]);

            return $invoice->load('items');
        });

        return ApiResponse::success($invoice, 'Invoice created', 201);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $this->assertBusinessModel($request, $invoice);

        return ApiResponse::success($invoice->load(['items', 'payments']));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->assertBusinessModel($request, $invoice);
        $invoice->update($request->validate([
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($invoice, 'Invoice updated');
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $this->assertBusinessModel($request, $invoice);
        $invoice->delete();

        return ApiResponse::success(null, 'Invoice deleted');
    }

    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $this->assertBusinessModel($request, $invoice);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $invoice, $data) {
            InvoicePayment::create(array_merge($data, ['invoice_id' => $invoice->id]));
            $invoice->paid_amount = (float) $invoice->paid_amount + (float) $data['amount'];
            $invoice->balance_amount = max((float) $invoice->grand_total - (float) $invoice->paid_amount, 0);
            $invoice->status = $invoice->balance_amount <= 0 ? 'paid' : 'partial';
            $invoice->save();

            Transaction::create([
                'business_id' => $invoice->business_id,
                'party_id' => $invoice->party_id,
                'type' => 'payment',
                'amount' => $data['amount'],
                'date' => $data['date'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'description' => 'Invoice payment '.$invoice->invoice_number,
                'created_by' => $request->user()->id,
                'source_type' => Invoice::class,
                'source_id' => $invoice->id,
            ]);
        });

        return ApiResponse::success($invoice->fresh()->load('payments'), 'Payment recorded');
    }
}
