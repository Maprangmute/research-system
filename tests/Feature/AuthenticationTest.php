<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_authenticated_users_without_current_terms_are_redirected_to_terms_gate(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('terms.accept', absolute: false));
    }

    public function test_terms_acceptance_is_recorded_after_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('terms.accept.store'), [
            'terms_accepted' => '1',
        ]);

        $user->refresh();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertSame(config('terms.current_version'), $user->terms_accepted_version);
        $this->assertNotNull($user->terms_accepted_ip);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_resigned_users_can_not_authenticate(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_RESIGNED,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }
}
