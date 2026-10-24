<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        $q = Expense::where('business_id', $this->businessId($request));
        if ($from = $request->query('from_date')) {
            $q->whereDate('date', '>=', $from);
        }
        if ($to = $request->query('to_date')) {
            $q->whereDate('date', '<=', $to);
        }

        return ApiResponse::paginated($q->latest('date')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense = Expense::create(array_merge($data, [
            'business_id' => $this->businessId($request),
            'created_by' => $request->user()->id,
        ]));

        return ApiResponse::success($expense, 'Expense recorded', 201);
    }

    public function show(Request $request, Expense $expense): JsonResponse
    {
        $this->assertBusinessModel($request, $expense);

        return ApiResponse::success($expense);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $this->assertBusinessModel($request, $expense);
        $expense->update($request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($expense, 'Expense updated');
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $this->assertBusinessModel($request, $expense);
        $expense->delete();

        return ApiResponse::success(null, 'Expense deleted');
    }
}
