<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BookmarkNodeObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @OA\Schema(
 *     schema="BookmarkNode",
 *     type="object",
 *     title="BookmarkNode",
 *     @OA\Property(property="id", type="integer", example=9),
 *     @OA\Property(property="node_id", type="string", description="Foreign key representing the GitHub node_id of the user", example="MDQ6VXNlcjY3Mjk2MDg="),
 *     @OA\Property(property="resource_node_id", type="integer", description="Foreign key representing the ID of the bookmarked resource in resources_node", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
 * )
 */
#[ObservedBy([BookmarkNodeObserver::class])]
class BookmarkNode extends Model
{
    use HasFactory;

    protected $table = 'bookmarks_node';

    protected $fillable = ['node_id', 'resource_node_id'];

    public function resourceNode()
    {
        return $this->belongsTo(ResourceNode::class, 'resource_node_id', 'id');
    }

    public function roleNode()
    {
        return $this->belongsTo(RoleNode::class, 'node_id', 'node_id');
    }
}
