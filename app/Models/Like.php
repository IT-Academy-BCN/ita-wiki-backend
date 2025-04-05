<?php

declare (strict_types= 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Observers\LikeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @OA\Schema(
 *     schema="Like",
 *     type="object",
 *     title="Like",
 *     @OA\Property(property="id", type="integer", example=9),
 *     @OA\Property(property="github_id", type="integer", description="Foreign key representing the GitHub ID of the user", example=6729608),
 *     @OA\Property(property="resource_id", type="integer", description="Foreign key representing the ID of the liked resource", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
 * )
 */

#[ObservedBy([LikeObserver::class])]
class Like extends Model
{
    use HasFactory;
    
    protected $table = 'likes';
    protected $fillable = ['github_id', 'resource_id'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'github_id', 'github_id');
    }
}
