<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'identifier' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('projects', 'identifier')->ignore($projectId)],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,closed,archived'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
