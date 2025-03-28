<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_validation_when_required_fields_are_missing()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => '',
            'password' => 'A_a123123123',
            'password_confirmation' => 'A_a123123123',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['name' => ['فیلد نام الزامی است.']])
            ->assertJsonValidationErrors(['name'])->assertJson(['errors' => [
                'name' => ['فیلد نام الزامی است.'],
                'email' => ["فیلد آدرس ایمیل الزامی است."]
            ]]);
    }

    public function test_fails_validation_when_password_is_not_confirmed()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'ali',
            'email' => 'ali@gmail.com',
            'password' => 'A_a123123123',
            'password_confirmation' => 'A_a12312312',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('password')
            ->assertJsonFragment(['password' => ["رمز عبور و تایید مطابقت ندارد."]]);
    }

    public function test_register_fails_with_duplicate_email()
    {
        User::factory()->create([
            'name' => 'ali',
            'email' => 'ali@gmail.com',
            'password' => 'A_a123123123'
        ]);
        $response = $this->postJson('api/v1/register', [
            'name' => 'ali',
            'email' => 'ali@gmail.com',
            'password' => 'A_a123123123',
            'password_confirmation' => 'A_a123123123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('email')
            ->assertJsonFragment(['email' => ["آدرس ایمیل از قبل وجود دارد."]]);
    }

    public function test_user_can_register_successfully()
    {
        $response = $this->postJson('api/v1/register', [
            'name' => 'mohammad',
            'email' => 'mohammad@gmail.com',
            'password' => 'A_a123123123',
            'password_confirmation' => 'A_a123123123',
        ]);

        $response->assertStatus(201)->assertJson([
            'success' => true,
            'message' => 'ثبت نام با موفقیت انجام شد',
        ])->assertJsonStructure([
            'data' => ['user', 'api_token'],
            'success',
            'message'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'mohammad',
            'email' => 'mohammad@gmail.com',
        ]);
    }
}
