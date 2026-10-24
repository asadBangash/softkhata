<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\BusinessContext;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function __construct(private BusinessContext $businessContext) {}

    public function index(Request $request): JsonResponse
    {
        $businesses = $request->user()->businesses()->get();

        return ApiResponse::success($businesses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $business = Business::create($data);
        $ownerRoleId = \App\Models\Role::where('name', 'owner')->value('id');
        $business->users()->attach($request->user()->id, [
            'role_id' => $ownerRoleId,
            'status' => 'active',
        ]);

        return ApiResponse::success($business, 'Business created', 201);
    }

    public function show(Request $request, Business $business): JsonResponse
    {
        $this->assertAccess($request, $business);

        return ApiResponse::success($business);
    }

    public function update(Request $request, Business $business): JsonResponse
    {
        $this->assertAccess($request, $business);
        $business->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($business, 'Business updated');
    }

    public function destroy(Request $request, Business $business): JsonResponse
    {
        $this->assertAccess($request, $business);
        $business->delete();

        return ApiResponse::success(null, 'Business deleted');
    }

    public function switch(Request $request, Business $business): JsonResponse
    {
        $this->assertAccess($request, $business);
        $request->user()->update(['current_business_id' => $business->id]);

        return ApiResponse::success($business, 'Business switched');
    }

    private function assertAccess(Request $request, Business $business): void
    {
        abort_unless(
            $request->user()->businesses()->where('businesses.id', $business->id)->exists(),
            403,
            'No access to this business.'
        );
    }
}
