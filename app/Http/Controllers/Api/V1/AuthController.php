<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);

            $ownerRole = Role::where('name', 'owner')->firstOrFail();

            $business = Business::create([
                'name' => $data['business_name'],
                'owner_name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'category' => $data['business_category'] ?? null,
                'currency' => $data['currency'] ?? 'PKR',
            ]);

            $business->users()->attach($user->id, ['role_id' => $ownerRole->id, 'status' => 'active']);
            $user->update(['current_business_id' => $business->id]);

            return $user->fresh(['businesses']);
        });

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return ApiResponse::error('Invalid email or password', null, 401);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return ApiResponse::success([
            'user' => $user->load('businesses'),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user()->load('businesses'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'email', 'max:255'],
        ]);

        $user = $request->user();
        $user->fill($data);
        $user->save();

        return ApiResponse::success($user, 'Profile updated');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return ApiResponse::error('Current password is incorrect', null, 422);
        }

        $user->password = $data['password'];
        $user->save();

        return ApiResponse::success(null, 'Password changed successfully');
    }
}
