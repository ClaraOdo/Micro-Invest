<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {

        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])
            ->assertJson([
                'status'  => 'success',
                'message' => 'User registered successfully',
            ]);

        // Check if user exists in database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name'  => 'Test User',
        ]);
    }
    public function test_user_cannot_register_with_invalid_data()
    {
        // Test with missing fields
        $response = $this->postJson('/api/register', [
            'name'     => 'Test User',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test with existing email
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test with password mismatch
        $response = $this->postJson('/api/register', [
            'name'                  => 'Test User',
            'email'                 => 'new@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])
            ->assertJson([
                'status'  => 'success',
                'message' => 'User logged in successfully',
            ]);
    }
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Test with incorrect password
        $response = $this->postJson('/api/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test with non-existent email
        $response = $this->postJson('/api/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
    public function test_user_can_logout()
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'User logged out successfully',
            ]);

        // Verify token was deleted
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }
}
