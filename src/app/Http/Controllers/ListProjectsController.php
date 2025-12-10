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

    /**
     * @OA\Get(
     *   path="/api/codeconnect",
     *   summary="Get all projects",
     *   tags={"Codeconnect"},
     *   description="Returns a list of all published projects.",
     *   @OA\Response(
     *       response=200,
     *       description="List of projects retrieved successfully",
     *       @OA\JsonContent(
     *           type="array",
     *           @OA\Items(
     *               type="object",
     *               @OA\Property(property="id", type="integer", example=1),
     *               @OA\Property(property="title", type="string", example="Project Alpha"),
     *               @OA\Property(property="time_duration", type="string", example="2 months"),
     *               @OA\Property(property="language_backend", type="string", example="PHP"),
     *               @OA\Property(property="language_frontend", type="string", example="JavaScript"),
     *               @OA\Property(
     *                   property="contributors",
     *                   type="array",
     *                   @OA\Items(
     *                       type="object",
     *                       @OA\Property(property="name", type="string", example="John Doe"),
     *                       @OA\Property(property="programming_role", type="string", example="Backend Developer")
     *                   )
     *               )
     *           )
     *       )
     *   )
     * )
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

    /**
     * @OA\Get(
     *   path="/api/codeconnect/{id}",
     *   summary="Get a specific project",
     *   tags={"Codeconnect"},
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Project retrieved successfully",
     *      @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="title", type="string", example="Project Alpha"),
     *           @OA\Property(property="time_duration", type="string", example="1 month"),
     *           @OA\Property(property="language_backend", type="string", example="PHP"),
     *           @OA\Property(property="language_frontend", type="string", example="JavaScript"),
     *           @OA\Property(
     *               property="contributors",
     *               type="array",
     *               @OA\Items(
     *                   type="object",
     *                   @OA\Property(property="name", type="string", example="Jane Doe"),
     *                   @OA\Property(property="programming_role", type="string", example="Frontend Developer")
     *               )
     *           )
     *      )
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Project not found"
     *   )
     * )
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


    /**
     * @OA\Post(
     *   path="/api/codeconnect",
     *   summary="Create a new project",
     *   tags={"Codeconnect"},
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"title","time_duration","language_backend","language_frontend"},
     *          @OA\Property(property="title", type="string", example="Project Delta"),
     *          @OA\Property(property="time_duration", type="string", example="3 months"),
     *          @OA\Property(property="language_backend", type="string", example="PHP"),
     *          @OA\Property(property="language_frontend", type="string", example="JavaScript")
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Project created successfully"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Invalid language value"
     *   ),
     *   @OA\Response(
     *      response=422,
     *      description="Validation error"
     *   )
     * )
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
                'message' => 'Project created successfully',
                'data' => $newProject
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/codeconnect/{id}",
     *   summary="Update an existing project",
     *   tags={"Codeconnect"},
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          @OA\Property(property="title", type="string", example="Updated Project"),
     *          @OA\Property(property="time_duration", type="string", example="2 months"),
     *          @OA\Property(property="language_backend", type="string", example="PHP"),
     *          @OA\Property(property="language_frontend", type="string", example="TypeScript")
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Project updated successfully"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Project not found"
     *   )
     * )
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
                'message' => 'Project updated successfully',
                'data' => $projectUpdated
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

    /**
     * @OA\Delete(
     *   path="/api/codeconnect/{id}",
     *   summary="Delete a project",
     *   tags={"Codeconnect"},
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Project deleted successfully"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Project not found"
     *   )
     * )
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
