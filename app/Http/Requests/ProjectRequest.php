<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For store method, always allow
        if ($this->isMethod('post')) {
            return true;
        }

        // For update/delete, check if user owns the project
        $projectId = $this->route('id');
        $project = \App\Models\Project::find($projectId);
        return $project && Auth::id() === $project->user_id;
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
        ];

        // For store method, require user_id
        if ($this->isMethod('post')) {
            // When creating, set user_id to authenticated user
            $this->merge(['user_id' => Auth::id()]);
        }

        return $rules;
    }
}
