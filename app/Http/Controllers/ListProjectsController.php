<?php

declare (strict_types= 1);

namespace App\Http\Controllers;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use Illuminate\Http\Request;
use App\Enums\LanguageEnum;
use App\Http\Requests\ListProjectRequest;

class ListProjectsController extends Controller
{

    /** method index 
     * Route is GET /api/listsProject
     * Returns a Json response with a list of projects
     * return success true, data with list of projects, status 200 and message is 'List of projects retrieved successfully'
     */
    
    public function index(Request $request){

        $projects = ListProjects::with('contributorListProject.user')->get()->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'time_duration' => $project->time_duration,
                'language_backend' => $project->language_backend,
                'language_frontend' => $project->language_frontend,
                'contributors' => $project->contributorListProject->map(function ($contributor) {
                    return [
                        'name' => $contributor->user->name,
                        'programming_role' => $contributor->programming_role,
                    ];
                }),
            ];
        });
    
        return response()->json([
            'success'=>true,
            'data' => $projects,
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

    if(!$project){
        return response()->json([
            'success'=>false,
            'message' => 'Project not found'
        ], 404);
    }
    
    $project = [

        'title' => $project->title,
        'time_duration' => $project->time_duration,
        'language_backend' => $project->language_backend,
        'language_frontend' => $project->language_frontend,
        'contributors' => $project->contributorListProject->map(function ($contributor) {
            return [
                'name' => $contributor->user->name,
                'programming_role' => $contributor->programming_role
            ];
        }),
    ];

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

    public function store(ListProjectRequest $request){

        $validatedData = $request->validated();
        
        if (!in_array($validatedData['language_backend'], LanguageEnum::values())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Backend language'
            ], 400);
        }

        if (!in_array($validatedData['language_frontend'], LanguageEnum::values())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Frontend language'
            ], 400);
        }

        try{
            $newProject = ListProjects::create($validatedData);
            return response()->json([
                'success'=>true,
                'data' => $newProject,
                'message' => 'Project created successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
    
        }
    }

    /** method update
     * Route is PUT /api/listsProject/{id}
     * Updates a specific project and returns a Json response
     * return success true, status 200 and message is 'Project updated successfully'
     */

    public function update(ListProjectRequest $request, $id){

        $projectUpdated = ListProjects::find($id);

        if (!$projectUpdated) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $validatedData = $request->validated();

        if (!in_array($validatedData['language_backend'], LanguageEnum::values())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Backend language'
            ], 400);
        }

        if (!in_array($validatedData['language_frontend'], LanguageEnum::values())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Frontend language'
            ], 400);
        }
            
        try {
            $projectUpdated->update($validatedData);
            return response()->json([

                'success'=>true,
                'data' => $projectUpdated,
                'message' => 'Project updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating project',
                'message' => $e->getMessage()
            ], 500);
        }

    }

    /** Method destroy
     * Route is DELETE /api/listsProject/{id}
     * destroy a specific project and returns a Json response
     * return success true, status 200 and message is 'Project deleted successfully'
     */

    public function destroy($id){
        $projectDeleted = ListProjects::find($id);

            if(!$projectDeleted){
                return response()->json([
                    'success'=>false,
                    'message' => 'Project not found'
                ], 404);
            }
        try {
            // remove the contributors associated with the project
            ContributorListProject::where('list_project_id', $projectDeleted->id)->delete();
            $projectDeleted->delete();

             return response()->json([
                'success'=>true,
                'message' => 'Project deleted successfully'
            ], 200);
        }
    
        catch (\Exception $e) {
            return response()->json([
                'error' => 'Error deleting project',
                'message' => $e->getMessage()
            ], 500);
        }
        
    }
}