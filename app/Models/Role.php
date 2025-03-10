<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
* @OA\Schema(
*     schema="Role",
*     type="object",
*     description="Role object representing a user's role and associated GitHub ID",
*     @OA\Property(property="github_id", type="integer", description="The GitHub ID of the user", example=6729608),
*     @OA\Property(property="role", type="string", description="The role of the user", example="admin"),
*     @OA\Property(property="isAdmin", type="boolean", description="Check if the user is an admin", example=true),
*     @OA\Property(property="isStudent", type="boolean", description="Check if the user is a student", example=false),
*     @OA\Property(property="isMentor", type="boolean", description="Check if the user is a mentor", example=false),
*     @OA\Property(property="isAnonymous", type="boolean", description="Check if the user is anonymous", example=false)
* )
*/
class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = [
        'github_id',
        'role'
    ];

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // Since we have not implemented middleware, the following may be useful

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }


    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    public function isAnonymous(): bool
    {
        return $this->role === 'anonymous';
    }
}
