<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UpdateRoleTest extends TestCase
{
    protected $student;
    protected $mentor;
    protected $admin;
    protected $superadmin;

    public function setUp(): void
    {
        parent::setUp();

        // Crea los roles si no existen
        foreach (['student', 'mentor', 'admin', 'superadmin'] as $role) {
            Role::findOrCreate($role);
        }

        // Crea usuarios y asigna roles
        $this->student = User::factory()->create(['github_id' => 123456]);
        $this->student->assignRole('student');

        $this->mentor = User::factory()->create(['github_id' => 234567]);
        $this->mentor->assignRole('mentor');

        $this->admin = User::factory()->create(['github_id' => 345678]);
        $this->admin->assignRole('admin');

        $this->superadmin = User::factory()->create(['github_id' => 456789]);
        $this->superadmin->assignRole('superadmin');
    }

    public function testCanUpdateRoleToLower(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->admin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ])->assertStatus(200);

        $this->assertTrue($this->student->fresh()->hasRole('mentor'));
    }

    public function testCannotUpdateHigherRankedRole(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->mentor->github_id,
            'github_id' => $this->admin->github_id,
            'role' => 'student'
        ])->assertStatus(403);

        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
    }

    public function testCannotUpdateRoleToEqual(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->superadmin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'superadmin'
        ])->assertStatus(403);

        $this->assertFalse($this->student->fresh()->hasRole('superadmin'));
    }

    public function testCannotUpdateRoleToHigher(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->mentor->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'admin'
        ])->assertStatus(403);

        $this->assertFalse($this->student->fresh()->hasRole('admin'));
    }

    public function testCannotUpdateRoleToNonExistent(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->admin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ])->assertStatus(422);

        $this->assertFalse($this->student->fresh()->hasRole('nonexistent'));
    }

    public function testCannotUpdateRoleWithNonExistentAuthorized(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => 999999,
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ])->assertStatus(422);

        $this->assertFalse($this->student->fresh()->hasRole('mentor'));
    }
}
