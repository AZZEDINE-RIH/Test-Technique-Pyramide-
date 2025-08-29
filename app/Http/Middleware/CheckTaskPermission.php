<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTaskPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $taskId = $request->route('id');
        
        if ($taskId) {
            $task = Task::find($taskId);
            
            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }
            
            // Allow if user is the project owner or the assigned user
            if ($task->project->user_id !== Auth::id() && $task->assigned_to !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized. You cannot modify this task.'], 403);
            }
            
            // Log pour le débogage
            \Illuminate\Support\Facades\Log::info('CheckTaskPermission middleware', [
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'project_owner_id' => $task->project->user_id,
                'assigned_to' => $task->assigned_to,
                'method' => $request->method(),
                'path' => $request->path()
            ]);
        }
        
        return $next($request);
    }
}