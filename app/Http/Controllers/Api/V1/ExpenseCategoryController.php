<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ExpenseCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            ExpenseCategory::orderBy('name')->get(['id', 'name'])
        );
    }
}
