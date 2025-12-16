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

    /**
     * @OA\Patch(
     *     path="/api/codeconnect/{listProject}/contributors/{contributor}/status",
     *     summary="Update contributor status",
     *     tags={"Contributors"},
     *     description="Updates the status of a contributor (accept or reject)",
     *     @OA\Parameter(
     *         name="listProject",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="contributor",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"accepted", "rejected"}, example="accepted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contributor status updated successfully"),
     *             @OA\Property(property="status", type="string", example="accepted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Status must be accepted or rejected")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="You cannot validate your own request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contributor not found")
     *         )
     *     )
     * )
     */
    public function updateContributorStatus(Request $request, int $listProjectId, int $contributorId)
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

    /**
     * @OA\Get(
     *     path="/api/codeconnect/{listProject}/contributors",
     *     summary="Get all contributors of a project",
     *     tags={"Contributors"},
     *     description="Returns a list of all contributors for a specific project",
     *     @OA\Parameter(
     *         name="listProject",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contributors retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="programming_role", type="string", example="Frontend Developer"),
     *                     @OA\Property(property="status", type="string", example="pending")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found")
     *         )
     *     )
     * )
     */
    public function getContributors(int $listProjectId)
    {
        $project = ListProjects::find($listProjectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $contributors = ContributorListProject::where('list_project_id', $listProjectId)
            ->with('user')
            ->get()
            ->map(function ($contributor) {
                return [
                    'id' => $contributor->id,
                    'user_id' => $contributor->user_id,
                    'programming_role' => $contributor->programming_role,
                    'status' => $contributor->status,
                    'user' => [
                        'id' => $contributor->user->id,
                        'name' => $contributor->user->name,
                        'email' => $contributor->user->email,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $contributors,
            'message' => 'Contributors retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/codeconnect/{listProject}/contributors",
     *     summary="Create a contributor request",
     *     tags={"Contributors"},
     *     description="Creates a new contributor request for a project with pending status",
     *     @OA\Parameter(
     *         name="listProject",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","programming_role"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="programming_role", type="string", enum={"Frontend Developer", "Backend Developer", "Fullstack Developer", "Other"}, example="Frontend Developer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contributor request created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="programming_role", type="string", example="Frontend Developer"),
     *                 @OA\Property(property="list_project_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User is already a contributor for this project")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Project not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function addContributor(Request $request, int $listProjectId)
    {
        $validatedData = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'programming_role' => ['required', 'string', 'in:Frontend Developer,Backend Developer,Fullstack Developer,Other'],
        ]);

        $project = ListProjects::find($listProjectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $existingContributor = ContributorListProject::where('list_project_id', $listProjectId)
            ->where('user_id', $validatedData['user_id'])
            ->first();

        if ($existingContributor) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a contributor for this project'
            ], 400);
        }

        try {
            $contributor = ContributorListProject::create([
                'user_id' => $validatedData['user_id'],
                'programming_role' => $validatedData['programming_role'],
                'list_project_id' => $listProjectId,
                'status' => ContributorStatusEnum::Pending->value,
            ]);

            return response()->json([
                'success' => true,
                'data' => $contributor,
                'message' => 'Contributor request created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error creating contributor request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/codeconnect/{listProject}/contributors/{contributor}",
     *     summary="Remove a contributor from a project",
     *     tags={"Contributors"},
     *     description="Deletes a contributor from a specific project",
     *     @OA\Parameter(
     *         name="listProject",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="contributor",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contributor removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Contributor not found")
     *         )
     *     )
     * )
     */
    public function removeContributor(int $listProjectId, int $contributorId)
    {
        $contributor = ContributorListProject::where('id', $contributorId)
            ->where('list_project_id', $listProjectId)
            ->first();

        if (!$contributor) {
            return response()->json([
                'success' => false,
                'message' => 'Contributor not found'
            ], 404);
        }

        try {
            $contributor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contributor removed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error removing contributor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
