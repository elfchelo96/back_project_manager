<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Task::class);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'category_id' => ['nullable', 'integer', 'exists:task_categories,id'],
            'status_id' => ['nullable', 'integer', 'exists:task_statuses,id'],
            'priority_id' => ['nullable', 'integer', 'exists:task_priorities,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'done_ratio' => ['nullable', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*.task_id' => ['required', 'integer', 'exists:tasks,id'],
            'dependencies.*.type' => ['nullable', 'in:blocks,relates_to'],
        ];
    }
}
