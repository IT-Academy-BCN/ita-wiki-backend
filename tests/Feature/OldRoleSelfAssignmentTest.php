<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OldRole;

class OldRoleSelfAssignmentTest extends TestCase
{
    protected $student;

    public function setUp(): void
    {
        parent::setUp();
        config(['feature_flags.allow_role_self_assignment' => true]);

        $this->student = OldRole::factory()->create([
            'github_id' => random_int(1001, 9999999),
            'role' => 'student'
        ]);
    }

    public function testCanSelfAssignRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment'), [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);
    }

    public function testCannotSelfAssignANonExistentRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment'), [
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'nonexistent'
        ]);

        $this->assertDatabaseHas('roles', [
            'github_id' => $this->student->github_id,
            'role' => 'student'
        ]);
    }
}
