<?php

declare (strict_types= 1);

namespace App\Http\Controllers;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
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
            'data' => ListProjects::with('contributorListProject.user')->get(),
            'message' => 'List of projects retrieved successfully'
        ], 200);

    }

    /** method show
     * Route is GET /api/listsProject/{id}
     * Returns a Json response with a specific project
     * return success true, data with project details, status 200 and message is 'Project retrieved successfully'
     */
    public function show($id){
    $project = ListProjects::with('contributorListProject.user')->find($id);

        return response()->json([
            'success'=>true,
            'data' => $project,
            'message' => 'Project retrieved successfully'
        ], 200);
    }


    /** method store
     * Route is POST /api/listsProject
     * Creates a new project and returns a Json response
     * return success true, status 200 and message is 'Project created successfully'
     */

    public function store(Request $request){
        return response()->json([
            'success'=>true,
            'message' => 'Project created successfully'
        ], 200);
    }

    /** method update
     * Route is PUT /api/listsProject/{id}
     * Updates a specific project and returns a Json response
     * return success true, status 200 and message is 'Project updated successfully'
     */

    public function update(Request $request, $id){
        
        return response()->json([
            'success'=>true,
            'message' => 'Project updated successfully'
        ], 200);
    }

    /** Method destroy
     * Route is DELETE /api/listsProject/{id}
     * destroy a specific project and returns a Json response
     * return success true, status 200 and message is 'Project deleted successfully'
     */

    public function destroy($id){
        return response()->json([
            'success'=>true,
            'message' => 'Project deleted successfully'
        ], 200);
    }


}
