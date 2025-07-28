<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OldRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;
    
    protected $student;
    protected $mentor;
    protected $admin;
    protected $superadmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->student = OldRole::factory()->create([
            'github_id' => 123456,
            'role' => 'student'
        ]);
        $this->mentor = OldRole::factory()->create([
            'github_id' => 234567,
            'role' => 'mentor'
        ]);
        $this->admin = OldRole::factory()->create([
            'github_id' => 345678,
            'role' => 'admin'
        ]);
        $this->superadmin = OldRole::factory()->create([
            'github_id' => 456789,
            'role' => 'superadmin'
        ]);
    }

    public function testCanUpdateRoleToLower(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->admin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ])->assertStatus(200);

        $this->assertDatabaseHas('old_roles', [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);
    }

    public function testCannotUpdateHigherRankedRole(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->mentor->github_id,
            'github_id' => $this->admin->github_id,
            'role' => 'student'
        ])->assertStatus(403);

        $this->assertDatabaseHas('old_roles', [
            'github_id' => $this->admin->github_id,
            'role' => 'admin'
        ]);
    }

    public function testCannotUpdateRoleToEqual(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->superadmin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'superadmin'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'superadmin'
        ]);
    }

    public function testCannotUpdateRoleToHigher(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->mentor->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'admin'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'admin'
        ]);
    }

    public function testCannotUpdateRoleToNonExistent(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => $this->admin->github_id,
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ])->assertStatus(422);

        $this->assertDatabaseMissing('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ]);
    }

    public function testCannotUpdateRoleWithNonExistentAuthorized(): void
    {
        $this->putJson(route('roles.update'), [
            'authorized_github_id' => 999999,
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ])->assertStatus(422);

        $this->assertDatabaseMissing('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);
    }
}
