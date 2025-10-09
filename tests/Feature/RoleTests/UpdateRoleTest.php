<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('DEPRECATED: OldRole system - Skipped for PR');
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

        $this->assertDatabaseMissing('old_roles', [
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

        $this->assertDatabaseMissing('old_roles', [
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

        $this->assertDatabaseMissing('old_roles', [
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

        $this->assertDatabaseMissing('old_roles', [
            'github_id' => $this->student->github_id,
            'role' => 'mentor'
        ]);
    }
}
