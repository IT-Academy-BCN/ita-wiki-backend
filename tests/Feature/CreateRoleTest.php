<?php

declare (strict_types= 1);

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CreateRoleTest extends TestCase
{
    private function createRole(string $role): User
    {
        // Crear el rol en Spatie si no existe
        SpatieRole::findOrCreate($role, 'web');
        $user = User::factory()->create([
            'github_id' => random_int(100000, 999999),
        ]);
        $user->assignRole($role);
        return $user;
    }

    private function requestCreateRole(int $github_id, string $role)
    {
        // Crear el rol en Spatie si no existe
        SpatieRole::findOrCreate($role, 'web');
        return $this->post(route('roles.create'), [
            'authorized_github_id' => $github_id,
            'github_id' => 123456,
            'role' => $role,
        ]);
    }
    
    public function testCanCreateRoleBeeingSuperadmin(): void
    {
        $superadmin = $this->createRole('superadmin');
        $this->requestCreateRole($superadmin->github_id, 'admin')
            ->assertStatus(201);
        $user = User::where('github_id', 123456)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->hasRole('admin'));
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'role_id' => SpatieRole::findByName('admin')->id,
            'model_type' => User::class,
        ]);
    }

    public function testCanCreateRoleBeeingAdmin(): void
    {
        $admin = $this->createRole('admin');
        $this->requestCreateRole($admin->github_id, 'mentor')
            ->assertStatus(201);
        $user = User::where('github_id', 123456)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->hasRole('mentor'));
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'role_id' => SpatieRole::findByName('mentor')->id,
            'model_type' => User::class,
        ]);
    }

    public function testCanCreateRoleBeeingMentor(): void
    {
        $mentor = $this->createRole('mentor');
        $this->requestCreateRole($mentor->github_id, 'student')
            ->assertStatus(201);
        $user = User::where('github_id', 123456)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->fresh()->hasRole('student'));
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'role_id' => SpatieRole::findByName('student')->id,
            'model_type' => User::class,
        ]);
    }

    public function testCanShowAnErrorWhenAnStudentWantsToCreateASuperiorRole()
    {
        $student = $this->createRole('student');
        $user = User::where('github_id', 123456)->first();
        $this->requestCreateRole($student->github_id, 'mentor')
            ->assertStatus(403);
        $this->assertFalse($user && $user->hasRole('mentor'));
    }

    public function testCanShowAnErrorWhenAnMentorWantsToCreateASuperiorRole()
    {
        $mentor = $this->createRole('mentor');
        $user = User::where('github_id', 123456)->first();
        $this->requestCreateRole($mentor->github_id, 'admin')
            ->assertStatus(403);
        $this->assertFalse($user && $user->hasRole('admin'));
    }

    public function testCanShowAnErrorWhenAnAdminWantsToCreateASuperiorRole()
    {
        $admin = $this->createRole('admin');
        $user = User::where('github_id', 123456)->first();
        $this->requestCreateRole($admin->github_id, 'superadmin')
            ->assertStatus(403);
        $this->assertFalse($user && $user->hasRole('superadmin'));
    }

    public function testCanshowStatus_422WhenGithubIdIsDuplicated()
    {
        $admin = $this->createRole('admin');
        $this->requestCreateRole($admin->github_id, 'student');
        // Intentar crear el mismo usuario y rol otra vez
        $this->requestCreateRole($admin->github_id, 'student')
            ->assertStatus(422);
    }    

    private function GetRoleData(): array
    {
        // Crear el rol en Spatie si no existe
        SpatieRole::findOrCreate('student', 'web');
        return [
            'authorized_github_id' => random_int(100000, 999999),
            'github_id' => random_int(100000, 999999),
            'role' => 'student',
        ];
    }    

    #[DataProvider('roleValidationProvider')]
    public function testItCanShowStatus_422WithInvalidData(array $invalidData, string $fieldName): void
    {
        $data = $this->GetRoleData();
        $data = array_merge($data, $invalidData);  

        $response = $this->postJson(route('roles.create'), $data)
            ->assertStatus(422)
        // This verifies that the field $fieldName exists in the response and has at least one error message.
        ->assertJsonPath($fieldName, function ($errors) {
            return is_array($errors) && count($errors) > 0;
        });
    }
    
    public static function roleValidationProvider(): array
    {
        return[
        // authorized_github_id
            'missing authorized_github_id' => [['authorized_github_id' => null], 'authorized_github_id'],
            'authorized_github_id must be an integer' => [['authorized_github_id' => 'string'], 'authorized_github_id'],
            'authorized_github_id must be greater than 1' => [['authorized_github_id' => 0], 'authorized_github_id'],
            'authorized_github_id must exist in roles table' => [['authorized_github_id' => 123456], 'authorized_github_id'],        
        // github_id
            'missing github_id' => [['github_id' => null], 'github_id'],
            'github_id must be an integer' => [['github_id' => 'string'], 'github_id'],
            'github_id must be greater than 1' => [['github_id' => 0], 'github_id'],        
        // role
            'missing role' => [['role' => null], 'role'],
            'role must be a string' => [['role' => 123456], 'role'],
            'role must be one of the following: superadmin, admin, mentor, student' => [['role' => 'visitor'], 'role'],
        ];
    }

}
