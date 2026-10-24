<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\BusinessContext;
use App\Services\DashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private BusinessContext $businessContext,
        private DashboardService $dashboard,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());

        return ApiResponse::success($this->dashboard->summary($businessId));
    }
}
