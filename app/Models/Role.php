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
*     @OA\Property(property="role", type="string", description="The role of the user", example="student")
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

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
