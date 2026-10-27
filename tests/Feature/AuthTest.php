<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_default_categories_and_wallets(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi@example.com')->firstOrFail();

        $this->assertSame(12, $user->categories()->count());
        $this->assertSame(3, $user->wallets()->count());
    }

    public function test_registration_rejects_a_weak_password(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'lemah@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'lemah@example.com']);
    }

    public function test_weak_password_cannot_be_set_from_the_profile_page(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $response = $this->actingAs($user)->from('/profile')->put('/profile/password', [
            'current_password' => 'Password123!',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ])->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_protected_pages(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/transactions')->assertRedirect(route('login'));
        $this->get('/reports')->assertRedirect(route('login'));
    }
}
