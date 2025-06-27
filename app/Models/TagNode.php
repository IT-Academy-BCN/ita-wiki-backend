<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="TagNode",
 *   type="object",
 *   title="TagNode",
 *   description="Tags used in resources_node",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="debugging"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
 * )
 */
class TagNode extends Model
{
    // use HasFactory; // Uncomment if you adding a factory in the future
    protected $table = 'tags_node';
    protected $fillable = ['name'];

    protected $casts = [
        'name' => 'string',
    ];
}
