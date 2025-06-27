<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\RoleNode;

class RoleSelfAssignmentNodeTest extends TestCase
{

    protected $student;

    public function setUp(): void
    {
        parent::setUp();
        config(['feature_flags.allow_role_self_assignment' => true]);

        $this->student = RoleNode::factory()->create([
            'node_id' => 'MDQ6VXNlcj123456=',
            'role' => 'student'
        ]);
    }

    public function testCanSelfAssignRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment-node'), [
            'node_id' => $this->student->node_id,
            'role' => 'mentor'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('roles_node', [
            'node_id' => $this->student->node_id,
            'role' => 'mentor'
        ]);
    }

    public function testCannotSelfAssignNonExistentRole(): void
    {
        $response = $this->putJson(route('feature-flags.role-self-assignment-node'), [
            'node_id' => $this->student->node_id,
            'role' => 'nonexistent'
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('roles_node', [
            'node_id' => $this->student->node_id,
            'role' => 'nonexistent'
        ]);

        $this->assertDatabaseHas('roles_node', [
            'node_id' => $this->student->node_id,
            'role' => 'student'
        ]);
    }


}
