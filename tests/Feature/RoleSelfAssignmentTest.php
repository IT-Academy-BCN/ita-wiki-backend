<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleSelfAssignmentTest extends TestCase
{
    protected $student;

    public function setUp(): void
    {
        parent::setUp();
        config(['feature_flags.allow_role_self_assignment' => true]);

        foreach (['student', 'mentor', 'admin', 'superadmin'] as $role) {
            SpatieRole::findOrCreate($role, 'web');
        }

        $this->student = User::factory()->create([
            'github_id' => random_int(1001, 9999999),
        ]);
        $this->student->assignRole('student');
    }

    public function testCanSelfAssignRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment'), [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);

        $response->assertStatus(200);

        $this->assertTrue($this->student->fresh()->hasRole('mentor'));
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $this->student->id,
            'role_id' => SpatieRole::findByName('mentor')->id,
            'model_type' => User::class,
        ]);
    }

    public function testCannotSelfAssignANonExistentRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment'), [
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ]);

        $response->assertStatus(422);

        $this->assertFalse($this->student->fresh()->hasRole('nonexistent'));
        $this->assertTrue($this->student->fresh()->hasRole('student'));
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $this->student->id,
            'role_id' => SpatieRole::findByName('student')->id,
            'model_type' => User::class,
        ]);
    }
}
