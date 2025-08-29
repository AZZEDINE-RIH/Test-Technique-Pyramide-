<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->route('id');
        \Illuminate\Support\Facades\Log::info('CheckProjectOwnership middleware', [
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'method' => $request->method(),
            'path' => $request->path()
        ]);
        
        if ($projectId) {
            $project = Project::find($projectId);
            
            if (!$project) {
                \Illuminate\Support\Facades\Log::warning('Project not found', ['project_id' => $projectId]);
                return response()->json(['message' => 'Project not found'], 404);
            }
            
            if ($project->user_id !== Auth::id()) {
                \Illuminate\Support\Facades\Log::warning('Unauthorized access to project', [
                    'project_id' => $projectId,
                    'project_owner_id' => $project->user_id,
                    'current_user_id' => Auth::id()
                ]);
                return response()->json(['message' => 'Unauthorized. You do not own this project.'], 403);
            }
            
            \Illuminate\Support\Facades\Log::info('Project ownership verified', ['project_id' => $projectId]);
        }
        
        return $next($request);
    }
}