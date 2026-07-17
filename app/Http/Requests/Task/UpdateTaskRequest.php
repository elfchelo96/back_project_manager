<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:task_categories,id'],
            'status_id' => ['sometimes', 'integer', 'exists:task_statuses,id'],
            'priority_id' => ['sometimes', 'integer', 'exists:task_priorities,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'subject' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'done_ratio' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
