<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ListProjects;
use App\Models\ContributorListProject;
use Illuminate\Http\Request;
use App\Enums\LanguageEnum;
use App\Http\Requests\ListProjectRequest;
use App\Enums\ContributorStatusEnum;

class ListProjectsController extends Controller
{

    /** method index 
     * Route is GET /api/listsProject
     * Returns a Json response with a list of projects
     * return success true, data with list of projects, status 200 and message is 'List of projects retrieved successfully'
     */

    public function index(Request $request)
    {

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
            'success' => true,
            'data' => $projects,
            'message' => 'List of projects retrieved successfully'
        ], 200);
    }

    /** method show
     * Route is GET /api/listsProject/{id}
     * Returns a Json response with a specific project
     * return success true, data with project details, status 200 and message is 'Project retrieved successfully'
     */
    public function show($id)
    {
        $project = ListProjects::with('contributorListProject.user')->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
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
            'success' => true,
            'data' => $project,
            'message' => 'Project retrieved successfully'
        ], 200);
    }


    /** method store
     * Route is POST /api/listsProject
     * Creates a new project and returns a Json response
     * return success true, status 200 and message is 'Project created successfully'
     */

    public function store(ListProjectRequest $request)
    {

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
            $newProject = ListProjects::create($validatedData);
            return response()->json([
                'success' => true,
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

    public function update(ListProjectRequest $request, $id)
    {

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

                'success' => true,
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

    /** method updateContributorStatus
     * Route is PATCH /api/listsProject/{listProject}/contributors/{contributor}/status
     * Updates the status of a specific contributor in a project and returns a Json response
     * return success true, status 200 and message is 'Contributor status updated successfully'
     */
    public function updateContributorStatus(Request $request, $listProjectId, $contributorId)
    {
        $validatedData = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $newStatus = $validatedData['status'];

        if (!in_array($newStatus, [
            ContributorStatusEnum::Accepted->value,
            ContributorStatusEnum::Rejected->value,
        ], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Status must be accepted or rejected',
            ], 400);
        }

        $user = auth()->user(); // Can be null for now

        $contributor = ContributorListProject::where('list_project_id', $listProjectId)
            ->where('id', $contributorId)
            ->first();

        if (!$contributor) {
            return response()->json([
                'success' => false,
                'message' => 'Contributor not found',
            ], 404);
        }

        //Only if is an authenticated user
        if ($user && $contributor->user_id === $user->id) {
            return response()->json([
                'error' => 'You cannot validate your own request',
            ], 403);
        }

        if ($contributor->status !== ContributorStatusEnum::Pending->value) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending contributors can be validated',
            ], 400);
        }

        if ($validatedData['status'] === ContributorStatusEnum::Pending->value) {
            return response()->json([
                'success' => false,
                'message' => 'Status must be accepted or rejected',
            ], 400);
        }

        $contributor->status = $validatedData['status'];
        $contributor->save();

        return response()->json([
            'success' => true,
            'message' => 'Contributor status updated successfully',
            'status' => $contributor->status,
            'data' => $contributor,
        ], 200);
    }

    /** Method destroy
     * Route is DELETE /api/listsProject/{id}
     * destroy a specific project and returns a Json response
     * return success true, status 200 and message is 'Project deleted successfully'
     */

    public function destroy($id)
    {
        $projectDeleted = ListProjects::find($id);

        if (!$projectDeleted) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }
        try {
            // remove the contributors associated with the project
            ContributorListProject::where('list_project_id', $projectDeleted->id)->delete();
            $projectDeleted->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error deleting project',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
