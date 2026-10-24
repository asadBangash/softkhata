<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/v1/login', [
            'email' => 'demo@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }
}
