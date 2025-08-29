<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendTaskAssignmentNotification;

class TaskController extends Controller
{
    // GET /api/projects/{id}/tasks
    public function index($id)
    {
        $project = Project::findOrFail($id);
        $tasks = $project->tasks()->with('assignedTo')->paginate(10);
        return TaskResource::collection($tasks);
    }
    
    // GET /api/tasks/{id}
    public function show($id)
    {
        $task = Task::with('assignedTo')->findOrFail($id);
        return new TaskResource($task);
    }

    // POST /api/projects/{id}/tasks
    public function store(TaskRequest $request, $id)
    {
        $project = Project::findOrFail($id);
        
        // Check if user owns the project
        if ($project->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $task = $project->tasks()->create($validated);
        
        // Dispatch job to send email notification
        SendTaskAssignmentNotification::dispatch($task);

        return new TaskResource($task);
    }

    // PUT /api/tasks/{id}
    public function update(TaskRequest $request, $id)
    {
        $task = Task::findOrFail($id);
        
        // Check if user is authorized to update this task
        if ($task->project->user_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $oldAssignedTo = $task->assigned_to;
        
        // Assurons-nous que assigned_to peut être null
        if (array_key_exists('assigned_to', $validated) && $validated['assigned_to'] === null) {
            // Garder assigned_to comme null
        } else if (empty($validated['assigned_to'])) {
            // Si assigned_to est vide mais pas explicitement null, le définir comme null
            $validated['assigned_to'] = null;
        }
        
        $task->update($validated);
        
        // If the assigned user has changed, send notification
        if (isset($validated['assigned_to']) && $oldAssignedTo !== $validated['assigned_to'] && $validated['assigned_to'] !== null) {
            SendTaskAssignmentNotification::dispatch($task);
        }

        return new TaskResource($task);
    }

    // DELETE /api/tasks/{id}
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        
        // Check if user is authorized to delete this task
        // Autoriser le propriétaire du projet ou l'utilisateur assigné à supprimer la tâche
        if ($task->project->user_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }

    // PATCH /api/tasks/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        // Check if user is authorized to update this task
        if ($task->project->user_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $task->update(['is_completed' => $validated['is_completed']]);

        return new TaskResource($task);
    }
}
