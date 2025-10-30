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
        'language_Backend',
        'language_Frontend'
    ];

    public function contributorListProject()
    {
        return $this->hasMany(ContributorListProject::class, 'list_project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
