<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_logout_successfully(): void
    {
        $user = User::factory()->create();

        $user->createToken('test-token')->plainTextToken;

        Sanctum::actingAs($user);

        $response = $this->postJson('api/v1/logout');

        $response->assertStatus(200)->assertJson([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد',
            'data' => null
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user::class
        ]);
    }
}
