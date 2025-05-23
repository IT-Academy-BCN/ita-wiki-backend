<?php
declare (strict_types= 1);

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *   schema="Tag",
 *   type="object",
 *   title="Tag",
 *   description="Tags used in resources",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="debugging"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
 * )
 */

class Tag extends Model
{
    //use HasFactory;
    protected $table = 'tags';
    protected $fillable = ['name'];

    protected $casts = [
        'name' => 'string',
    ];

  
}