<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_visitor_and_logs_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Иван Тестов',
            'email' => 'ivan@test.ru',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ivan@test.ru', 'role' => 'visitor']);
        $this->assertTrue(Hash::check('secret1', User::first()->password));
    }

    public function test_login_redirects_by_role(): void
    {
        User::factory()->create(['email' => 'org@test.ru', 'role' => 'organizer']);

        $this->post('/login', ['email' => 'org@test.ru', 'password' => 'password'])
            ->assertRedirect('/organizer/events');
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'a@test.ru']);

        $this->post('/login', ['email' => 'a@test.ru', 'password' => 'wrong'])->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::factory()->create(['email' => 'blocked@test.ru', 'is_active' => false]);

        $this->post('/login', ['email' => 'blocked@test.ru', 'password' => 'password']);

        $this->assertGuest();
    }
}
