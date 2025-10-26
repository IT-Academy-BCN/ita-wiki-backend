<?php

declare (strict_types= 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListProjects extends Model
{
    //
    protected $table = 'list_projects';
    protected $fillable = [
        'title',
        'time_duration',
        'lenguage_Backend',
        'lenguage_Frontend'
    ];
    
}
