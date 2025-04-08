<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


    /**
     * @OA\Schema(
     *      schema="Resource",
     *      type="object",
     *      title="Resource",
     *      @OA\Property(property="id", type="integer", example=1),
     *      @OA\Property(property="github_id", type="integer", example=12345),
     *      @OA\Property(property="title", type="string", nullable=true, example="Lorem Ipsum ..."),
     *      @OA\Property(property="description", type="string", nullable=true, example="Lorem Ipsum ..."),
     *      @OA\Property(property="url", type="string", nullable=true, example="https://www.hola.com", format="url"),
     *      @OA\Property(property="category", type="string", enum={"Node","React","Angular","JavaScript","Java","Fullstack PHP", "Data Science","BBDD"}, example="Node"),
     *      @OA\Property(property="theme", type="string", enum={"All","Components","UseState & UseEffect","Eventos","Renderizado condicional","Listas", "Estilos","Debugging", "React Router"}, example="All"),
     *      @OA\Property(property="type", type="string", enum={"Video","Cursos","Blog"}, example="Video"),
     *      @OA\Property(property="bookmark_count", type="integer", example = 1),
     *      @OA\Property(property="like_count", type="integer", example = 1),
     *      @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z"),
     *      @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-17T19:23:41.000000Z")
     * )
     */
class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceFactory> */
    use HasFactory;

    protected $fillable = [
        'github_id',
        'description',
        'title',
        'url',
        'category',
        'theme',
        'type',
        'bookmark_count',
        'like_count'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
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
