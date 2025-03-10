<?php

declare (strict_types= 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use HasFactory;
    protected $table = 'bookmarks';
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


