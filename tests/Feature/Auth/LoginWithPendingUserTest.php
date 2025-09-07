<?php

namespace Tests\Feature\Auth;

use App\Enums\Tenant\UserRegistrationStatus;
use App\Enums\Tenant\UserRole;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginWithPendingUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_receives_activation_message_on_login_attempt()
    {
        // Create a pending user (like a coach would)
        $pendingUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('random-temp-password'),
            'status' => UserRegistrationStatus::PENDING,
        ]);
        $pendingUser->assignRole(UserRole::CLIENT);

        // Attempt to login with wrong password (what a real user would do)
        $response = $this->post(route('tenant.login'), [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        // Should get the activation message instead of generic auth failed
        $response->assertSessionHasErrors([
            'email' => 'This account needs to be activated. Please complete your registration first.'
        ]);
    }

    public function test_accepted_user_gets_normal_auth_failed_message()
    {
        // Create an accepted user
        $acceptedUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('correct-password'),
            'status' => UserRegistrationStatus::ACCEPTED,
        ]);
        $acceptedUser->assignRole(UserRole::CLIENT);

        // Attempt to login with wrong password
        $response = $this->post(route('tenant.login'), [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        // Should get the normal auth failed message
        $response->assertSessionHasErrors([
            'email' => trans('auth.failed')
        ]);
    }

    public function test_nonexistent_user_gets_normal_auth_failed_message()
    {
        // Attempt to login with non-existent email
        $response = $this->post(route('tenant.login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ]);

        // Should get the normal auth failed message (don't reveal if email exists)
        $response->assertSessionHasErrors([
            'email' => trans('auth.failed')
        ]);
    }
}