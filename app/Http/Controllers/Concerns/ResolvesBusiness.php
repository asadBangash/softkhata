<?php

namespace App\Http\Controllers\Concerns;

use App\Services\BusinessContext;
use Illuminate\Http\Request;

trait ResolvesBusiness
{
    protected function businessId(Request $request): int
    {
        return app(BusinessContext::class)->resolveBusinessId($request->user());
    }

    protected function assertBusinessModel(Request $request, object $model, string $column = 'business_id'): void
    {
        abort_if((int) $model->{$column} !== $this->businessId($request), 403);
    }
}
