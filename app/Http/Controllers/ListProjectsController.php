<?php

declare (strict_types= 1);

namespace App\Http\Controllers;
use App\Models\ListProjects;
use Illuminate\Http\Request;

class ListProjectsController extends Controller
{

    /** method index 
     * Route is GET /api/listsProject
     * Returns a Json response with a list of projects
     * return success true, data with list of projects, status 200 and message is 'List of projects retrieved successfully'
     */
    public function index(Request $request){

        return response()->json([
            'success'=>true,
            'data' => ['All projects list here...'],
            'message' => 'List of projects retrieved successfully'
        ], 200);

    }

    /** method show
     * Route is GET /api/listsProject/{id}
     * Returns a Json response with a specific project
     * return success true, data with project details, status 200 and message is 'Project retrieved successfully'
     */
    public function show($id){
        $project = ListProjects::find($id);

        return response()->json([
            'success'=>true,
            'data' => $project,
            'message' => 'Project retrieved successfully'
        ], 200);
    }

}
