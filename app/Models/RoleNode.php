<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
* @OA\Schema(
*     schema="RoleNode",
*     type="object",
*     title="RoleNode",
*     description="RoleNode object representing a user's role and associated GitHub node_id",
*     @OA\Property(property="node_id", type="string", description="The GitHub node_id of the user", example="MDQ6VXNlcjY3Mjk2MDg="),
*     @OA\Property(property="role", type="string", description="The role of the user", example="student"),
*     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
*     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
* )
*/

class RoleNode extends Model
{
    use HasFactory;

    protected $table = 'roles_node';
    
    protected $fillable = 
    [
        'node_id',
        'role'
    ];

   
    public function resourcesNode()
    {
        return $this->hasMany(ResourceNode::class,  'role_node_id', 'node_id');
    }

    public function bookmarksNode()
    {
        return $this->hasMany(BookmarkNode::class,  'role_node_id', 'node_id');
    }

    
    //relationships (pending) to be implemented as those model classes are created
    // public function likesNode()
    // {
    //     return $this->hasMany(likesNode::class,  'role_node_id', 'node_id');
    // }


}

