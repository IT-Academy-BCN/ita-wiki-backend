<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalTest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'github_id',
        'title',
        'language',
        'description',
        'file_path',
        'file_original_name',
        'file_size',
        'tags',
        'bookmark_count',
        'like_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'file_size' => 'integer',
        'bookmark_count' => 'integer',
        'like_count' => 'integer',
    ];

    protected $dates = [
        'deleted_at',
    ];
}