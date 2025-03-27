<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'hosseinshirinegad98@gmail.com',
            'name' => 'Hossein',
            'password' => '123123123',
        ]);

        $response = $this->postJson('api/v1/login', [
            'email' => $user->email,
            'password' => '123123123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'api_token'],
            ])->assertJson(['success' => true]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'hosseinshirinegad98@gmail.com',
            'name' => 'Hossein',
            'password' => '123123123',
        ]);

        $response = $this->postJson('api/v1/login', [
            'email' => $user->email,
            'password' => '456456456',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'ایمیل یا رمز عبور اشتباه است']);
    }
}
