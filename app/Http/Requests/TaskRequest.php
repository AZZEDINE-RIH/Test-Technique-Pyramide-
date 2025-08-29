<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For store method (creating a new task)
        if ($this->isMethod('post')) {
            // Check if user owns the project
            $projectId = $this->route('id');
            $project = \App\Models\Project::find($projectId);
            return $project && Auth::id() === $project->user_id;
        }

        // For update/delete methods
        $taskId = $this->route('id');
        $task = Task::find($taskId);
        
        if (!$task) {
            return false;
        }
        
        // Allow if user is the project owner or the assigned user
        return Auth::id() === $task->project->user_id || Auth::id() === $task->assigned_to;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:todo,in_progress,done',
            'priority' => 'nullable|string|in:low,medium,high',
        ];

        // For status update only
        if ($this->is('api/tasks/*/status')) {
            return ['is_completed' => 'required|boolean'];
        }

        return $rules;
    }
}