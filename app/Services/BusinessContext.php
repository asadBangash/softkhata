<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BusinessContext
{
    public function __construct(private Request $request) {}

    public function resolveBusinessId(User $user): int
    {
        $headerId = $this->request->header('X-Business-Id');
        $businessId = $headerId ? (int) $headerId : (int) ($user->current_business_id ?? 0);

        if ($businessId <= 0) {
            $first = $user->businesses()->value('businesses.id');
            if (! $first) {
                throw new AccessDeniedHttpException('No business assigned to this user.');
            }
            $businessId = (int) $first;
        }

        if (! $user->businesses()->where('businesses.id', $businessId)->exists()) {
            throw new AccessDeniedHttpException('You do not have access to this business.');
        }

        return $businessId;
    }

    public function business(User $user): Business
    {
        return Business::findOrFail($this->resolveBusinessId($user));
    }
}
