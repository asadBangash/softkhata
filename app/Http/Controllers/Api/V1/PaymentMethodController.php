<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(PaymentMethod::where('is_active', true)->orderBy('name')->get());
    }
}
