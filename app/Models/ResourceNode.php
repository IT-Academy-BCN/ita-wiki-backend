<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="ResourceNode",
 *      type="object",
 *      title="ResourceNode",
 *      @OA\Property(property="id", type="integer", example=1),
 *      @OA\Property(property="node_id", type="string", example="MDQ6VXNlcjY3Mjk2MDg="),
 *      @OA\Property(property="title", type="string", nullable=true, example="Lorem Ipsum ..."),
 *      @OA\Property(property="description", type="string", nullable=true, example="Lorem Ipsum ..."),
 *      @OA\Property(property="url", type="string", nullable=true, example="https://www.hola.com", format="url"),
 *      @OA\Property(property="category", type="string", enum={"Node","React","Angular","JavaScript","Java","Fullstack PHP", "Data Science","BBDD"}, example="Node"),
 *      @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"kubernetes", "sql", "azure"}, description="Array of tags"),
 *      @OA\Property(property="type", type="string", enum={"Video","Cursos","Blog"}, example="Video"),
 *      @OA\Property(property="bookmark_count", type="integer", example=1),
 *      @OA\Property(property="like_count", type="integer", example=1),
 *      @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
 *      @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
 * )
 */
class ResourceNode extends Model
{
    use HasFactory;

    protected $table = 'resources_node'; 

    protected $fillable = [
        'node_id',
        'title',
        'description',
        'url',
        'category',
        'tags',
        'type',
        'bookmark_count',
        'like_count'
    ];

    protected $casts = [
        'tags' => 'array'
    ];

    public function roleNode()
    {
        return $this->belongsTo(RoleNode::class, 'node_id', 'node_id');
    }

    public function bookmarksNode()
    {
        return $this->hasMany(BookmarkNode::class, 'resource_node_id', 'id');
    }


/*
to be added later
    public function likesNode()
    {
        return $this->hasMany(LikeNode::class, 'resource_node_id', 'id');
    }
*/
}
