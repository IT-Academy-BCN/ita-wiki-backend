<?php

declare (strict_types= 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContributorListProject extends Model
{
    //
    protected $table = 'contributors_list_project';

    protected $fillable =[
        'github_id',
        'role',
        'list_project_id'
    ];

    /** Define the relationship to the Github model 
     * where github_id is the foreign key
     * return is information of the github user
    */

    public function github()
    {
        return $this->belongsTo(Github::class, 'github_id');
    }

    /** Define the relationship to the ListProjects model
     * where list_project_id is the foreign key
     * return is information of the list project
    */

    public function listProject()
    {
        return $this->belongsTo(ListProjects::class, 'list_project_id');
    }
}
