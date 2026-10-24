<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ResolvesBusiness;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    use ResolvesBusiness;

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated(
            Staff::where('business_id', $this->businessId($request))->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'job_title' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric'],
            'joining_date' => ['nullable', 'date'],
        ]);

        $staff = Staff::create(array_merge($data, ['business_id' => $this->businessId($request)]));

        return ApiResponse::success($staff, 'Staff created', 201);
    }

    public function show(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);

        return ApiResponse::success($staff);
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $staff->update($request->validate([
            'name' => ['sometimes', 'string'],
            'phone' => ['nullable', 'string'],
            'salary' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string'],
        ]));

        return ApiResponse::success($staff, 'Staff updated');
    }

    public function destroy(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $staff->delete();

        return ApiResponse::success(null, 'Staff deleted');
    }

    public function attendanceIndex(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);

        return ApiResponse::success(
            DB::table('staff_attendance')->where('staff_id', $staff->id)->orderByDesc('date')->limit(60)->get()
        );
    }

    public function attendanceStore(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $data = $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,leave,half_day'],
            'notes' => ['nullable', 'string'],
        ]);
        DB::table('staff_attendance')->updateOrInsert(
            ['staff_id' => $staff->id, 'date' => $data['date']],
            array_merge($data, ['updated_at' => now(), 'created_at' => now()])
        );

        return ApiResponse::success(null, 'Attendance saved');
    }

    public function salaryStore(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $data = $request->validate([
            'basic_salary' => ['required', 'numeric'],
            'net_salary' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);
        DB::table('staff_salaries')->insert(array_merge($data, [
            'staff_id' => $staff->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return ApiResponse::success(null, 'Salary recorded');
    }

    public function advanceStore(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $data = $request->validate(['amount' => ['required', 'numeric'], 'date' => ['required', 'date']]);
        DB::table('staff_advances')->insert(array_merge($data, [
            'staff_id' => $staff->id, 'created_at' => now(), 'updated_at' => now(),
        ]));

        return ApiResponse::success(null, 'Advance recorded');
    }

    public function bonusStore(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $data = $request->validate(['amount' => ['required', 'numeric'], 'date' => ['required', 'date']]);
        DB::table('staff_bonuses')->insert(array_merge($data, [
            'staff_id' => $staff->id, 'created_at' => now(), 'updated_at' => now(),
        ]));

        return ApiResponse::success(null, 'Bonus recorded');
    }

    public function overtimeStore(Request $request, Staff $staff): JsonResponse
    {
        $this->assertBusinessModel($request, $staff);
        $data = $request->validate([
            'hours' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ]);
        DB::table('staff_overtime')->insert(array_merge($data, [
            'staff_id' => $staff->id, 'created_at' => now(), 'updated_at' => now(),
        ]));

        return ApiResponse::success(null, 'Overtime recorded');
    }
}
