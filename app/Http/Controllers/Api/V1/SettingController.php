<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ResolvesBusiness;

    public function show(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $settings = BusinessSetting::firstOrCreate(['business_id' => $businessId]);

        return ApiResponse::success($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request);
        $settings = BusinessSetting::firstOrCreate(['business_id' => $businessId]);
        $settings->update($request->validate([
            'date_format' => ['nullable', 'string'],
            'invoice_prefix' => ['nullable', 'string'],
            'invoice_footer' => ['nullable', 'string'],
            'tax_enabled' => ['nullable', 'boolean'],
            'default_tax' => ['nullable', 'numeric'],
            'low_stock_alert' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($settings, 'Settings updated');
    }
}
