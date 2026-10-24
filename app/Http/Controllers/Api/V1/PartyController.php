<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Party\StorePartyRequest;
use App\Http\Requests\Party\UpdatePartyRequest;
use App\Models\Party;
use App\Services\BusinessContext;
use App\Services\LedgerService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function __construct(
        private BusinessContext $businessContext,
        private LedgerService $ledger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());

        $query = Party::where('business_id', $businessId);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'name');
        $query->orderBy($sort);

        $paginator = $query->paginate((int) $request->query('per_page', 20));

        $paginator->getCollection()->transform(function (Party $party) {
            $party->setAttribute('current_balance', $this->ledger->partyBalance($party));

            return $party;
        });

        return ApiResponse::paginated($paginator, 'Parties loaded');
    }

    public function store(StorePartyRequest $request): JsonResponse
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());
        $party = Party::create(array_merge($request->validated(), [
            'business_id' => $businessId,
        ]));
        $party->setAttribute('current_balance', $this->ledger->partyBalance($party));

        return ApiResponse::success($party, 'Party created successfully', 201);
    }

    public function show(Request $request, Party $party): JsonResponse
    {
        $this->assertPartyAccess($request, $party);
        $party->setAttribute('current_balance', $this->ledger->partyBalance($party));

        return ApiResponse::success($party);
    }

    public function update(UpdatePartyRequest $request, Party $party): JsonResponse
    {
        $this->assertPartyAccess($request, $party);
        $party->update($request->validated());
        $party->setAttribute('current_balance', $this->ledger->partyBalance($party));

        return ApiResponse::success($party, 'Party updated successfully');
    }

    public function destroy(Request $request, Party $party): JsonResponse
    {
        $this->assertPartyAccess($request, $party);
        $party->delete();

        return ApiResponse::success(null, 'Party deleted successfully');
    }

    public function ledger(Request $request, Party $party): JsonResponse
    {
        $this->assertPartyAccess($request, $party);

        $data = $this->ledger->ledger(
            $party,
            $request->query('from_date'),
            $request->query('to_date'),
        );

        return ApiResponse::success($data);
    }

    private function assertPartyAccess(Request $request, Party $party): void
    {
        $businessId = $this->businessContext->resolveBusinessId($request->user());
        abort_if($party->business_id !== $businessId, 403, 'Party not in selected business.');
    }
}
