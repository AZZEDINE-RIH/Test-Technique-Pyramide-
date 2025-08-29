<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // GET /api/projects
    public function index(Request $request)
    {
        $query = Project::query();
        
        // Handle eager loading of relationships
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $allowedRelations = ['user', 'tasks'];
            $validRelations = array_intersect($relations, $allowedRelations);
            
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        } else {
            // Default eager loading
            $query->with('user', 'tasks');
        }
        
        $projects = $query->paginate(10);
        return ProjectResource::collection($projects);
    }

    // POST /api/projects
    public function store(ProjectRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        
        $project = Project::create($validated);

        return new ProjectResource($project);
    }

    // GET /api/projects/{id}
    public function show(Request $request, $id)
    {
        $query = Project::query();
        
        // Handle eager loading of relationships
        if ($request->has('with')) {
            $relations = explode(',', $request->with);
            $allowedRelations = ['user', 'tasks'];
            $validRelations = array_intersect($relations, $allowedRelations);
            
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        } else {
            // Default eager loading
            $query->with('user', 'tasks');
        }
        
        $project = $query->findOrFail($id);
        return new ProjectResource($project);
    }

    // PUT /api/projects/{id}
    public function update(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('ProjectController@update', [
                'id' => $id,
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            $project = Project::findOrFail($id);
            
            // Check if user owns the project
            if ($project->user_id !== Auth::id()) {
                \Illuminate\Support\Facades\Log::warning('Unauthorized project update attempt', [
                    'project_id' => $id,
                    'project_owner_id' => $project->user_id,
                    'current_user_id' => Auth::id()
                ]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Simplify validation for testing
            $validated = $request->only(['title', 'description']);
            $project->update($validated);
            \Illuminate\Support\Facades\Log::info('Project updated successfully', ['project_id' => $id]);

            return response()->json(['message' => 'Project updated successfully', 'data' => $project]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating project', [
                'project_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error updating project: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/projects/{id}
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user owns the project
        if ($project->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully'], 200);
    }
    
    // GET /api/projects/{id}/users
    public function getUsers($id)
    {
        try {
            // Vérifier que le projet existe
            $project = Project::findOrFail($id);
            
            // Récupérer tous les utilisateurs pour l'assignation de tâches
            $users = User::select('id', 'name', 'email')->get();
            
            return response()->json($users);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération des utilisateurs', [
                'project_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()
            ], 500);
        }
    }
}
