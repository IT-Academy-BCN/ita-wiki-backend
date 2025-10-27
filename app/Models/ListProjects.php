<?php

declare (strict_types= 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ListProjects extends Model
{
    use HasFactory;
    protected $table = 'list_projects';
    protected $fillable = [
        'title',
        'time_duration',
        'lenguage_Backend',
        'lenguage_Frontend'
    ];
}
