<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        foreach (['/admin/users', '/organizer/events', '/participant/films', '/jury/films', '/visitor'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_each_role_opens_its_own_panel(): void
    {
        $panels = [
            'admin' => '/admin/users',
            'organizer' => '/organizer/events',
            'participant' => '/participant/films',
            'jury' => '/jury/films',
            'visitor' => '/visitor',
        ];

        foreach ($panels as $role => $url) {
            $this->actingAs($this->userWithRole($role))->get($url)->assertOk();
        }
    }

    public function test_role_cannot_open_foreign_panels(): void
    {
        $this->actingAs($this->userWithRole('visitor'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->userWithRole('visitor'))->get('/jury/films')->assertForbidden();
        $this->actingAs($this->userWithRole('participant'))->get('/organizer/events')->assertForbidden();
        $this->actingAs($this->userWithRole('jury'))->get('/organizer/reports')->assertForbidden();
        $this->actingAs($this->userWithRole('organizer'))->get('/admin/users')->assertForbidden();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $this->actingAs($this->userWithRole('jury'))->get('/dashboard')->assertRedirect('/jury/films');
        $this->actingAs($this->userWithRole('organizer'))->get('/dashboard')->assertRedirect('/organizer/events');
    }
}
