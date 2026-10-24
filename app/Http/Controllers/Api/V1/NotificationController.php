<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        $rows = BusinessNotification::where('business_id', $this->businessId($request))
            ->latest()
            ->paginate(20);

        return ApiResponse::paginated($rows);
    }

    public function destroy(Request $request, BusinessNotification $notification): JsonResponse
    {
        abort_if($notification->business_id !== $this->businessId($request), 403);
        $notification->delete();

        return ApiResponse::success(null, 'Notification deleted');
    }

    public function markRead(Request $request, BusinessNotification $notification): JsonResponse
    {
        abort_if($notification->business_id !== $this->businessId($request), 403);
        $notification->update(['read_at' => now()]);

        return ApiResponse::success($notification, 'Marked as read');
    }

    public function readAll(Request $request): JsonResponse
    {
        BusinessNotification::where('business_id', $this->businessId($request))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'All notifications marked read');
    }
}
