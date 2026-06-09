<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Conçu par')
            ->assertSee('https://www.fintchweb.com/');
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $this->post(route('register'), [
            'name' => 'Nouvel utilisateur',
            'email' => 'user@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }

    public function test_database_seeder_creates_configured_auth_user(): void
    {
        config()->set('auth.seed_user', [
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => 'seed-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Admin Test', $user->name);
        $this->assertTrue(Hash::check('seed-password', $user->password));
    }
}
